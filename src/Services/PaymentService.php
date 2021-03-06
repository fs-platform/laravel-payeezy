<?php

namespace Smbear\Payeezy\Services;

use Smbear\Payeezy\Enums\PayeezyEnum;
use Smbear\Payeezy\Traits\PayeezyOrder;
use Smbear\Payeezy\Exceptions\ApiException;

class PaymentService
{
    use PayeezyOrder;

    /**
     * @var array $config 配置文件
     */
    public $config;

    /**
     * @var array $order 订单信息
     */
    public $order;

    /**
     * @var array $headers 头信息
     */
    public $headers;

    /**
     * @var string body 信息
     */
    public $payload;

    /**
     * @var string $paymentType 付款类型
     */
    public $paymentType;

    /**
     * @var string $currencyCode 货币国家
     */
    public $currencyCode;

    /**
     * @var bool $isEuUnionCountry 是否是欧盟国家
     */
    public $isEuUnionCountry;

    /**
     * @var string $customerIpAddress 客户请求的ip地址
     */
    public $customerIpAddress;

    /**
     * @Notes:设置headers信息
     *
     * @return $this
     * @Author: smile
     * @Date: 2021/6/15
     * @Time: 19:33
     */
    protected function setHeaders(): self
    {
        if (is_null($this->headers)){
            $this->headers = [
                'Content-Type'  => 'application/json',
                'apikey'        => $this->config['apiKey'],
                'token'         => $this->config['merchantToken'],
                'Authorization' => $this->signature['authorization'],
                'nonce'         => $this->signature['nonce'],
                'timestamp'     => $this->signature['timestamp'],
            ];
        }

        return $this;
    }

    /**
     * @Notes:获取到3dx 下body信息
     *
     * @return array
     * @Author: smile
     * @Date: 2021/6/15
     * @Time: 19:34
     */
    public function get3DXPayload() : array
    {
        return [
            'merchant_ref'     => $this->order['ordersNumber'],
            'transaction_type' => 'purchase',
            'method'           => '3DS',
            'amount'           => strval($this->order['amount']),
            'currency_code'    => $this->currencyCode,
            'eci_indicator'    => $this->order['3ds']['ExtendedData']['ECIFlag'] ?? '',
            '3DS'              => [
                'type'                            => 'D',
                'program_protocol'                => '2',
                'directory_server_transaction_id' => $this->order['ds_transaction_id'] ?? '',
                'cardholder_name'                 => $this->order['card']['cardHolderName'],
                'exp_date'                        => $this->order['card']['exp_date'],
                'cavv'                            => $this->order['3ds']['ExtendedData']['CAVV'] ?? ''
            ],
            'token'            => [
                'token_type' => 'FDToken',
                'token_data' => [
                    'type'            => $this->order['card']['type'],
                    'value'           => $this->order['card']['value'],
                    'cardholder_name' => $this->order['card']['cardHolderName'],
                    'exp_date'        => $this->order['card']['exp_date']
                ]
            ],
            'billing_address'  => $this->order['billingAddress']
        ];
    }

    /**
     * @Notes:获取到token 下body信息
     *
     * @return array
     * @Author: smile
     * @Date: 2021/6/15
     * @Time: 19:34
     */
    public function getTokenPayload() : array
    {
        return [
            'merchant_ref'     => $this->order['ordersNumber'],
            'transaction_type' => 'purchase',
            'method'           => 'token',
            'amount'           => strval($this->order['amount']),
            'currency_code'    => $this->currencyCode,
            'token'            => [
                'token_type' => 'FDToken',
                'token_data' => [
                    'type'            => $this->order['card']['type'],
                    'value'           => $this->order['card']['value'],
                    'cardholder_name' => $this->order['card']['cardHolderName'],
                    'exp_date'        => $this->order['card']['exp_date']
                ]
            ],
            'billing_address'  => $this->order['billingAddress']
        ];
    }

    /**
     * @Notes:获取到integration 下body信息
     *
     * @return array
     * @Author: smile
     * @Date: 2021/6/20
     * @Time: 18:58
     */
    public function getIntegrationPayload(): array
    {
        return [
            'method'                   => 'token',
            'merchant_ref'             => $this->order['ordersNumber'],
            'amount'                   => strval($this->order['amount']),
            'currency_code'            => $this->currencyCode,
            'df_reference_id'          => $this->order['ordersId'],
            'device_channel'           => 'Browser',
            'authentication_indicator' => '01',
            'token'                    => [
                'token_type' => 'FDToken',
                'token_data' => [
                    'type'            => $this->order['card']['type'],
                    'cardholder_name' => $this->order['card']['cardHolderName'],
                    'exp_date'        => $this->order['card']['exp_date'],
                    'value'           => $this->order['card']['value']
                ]
            ],
            'billing_address' => $this->order['billingAddress'],
            'level3'          => [
                'ship_to_address' => $this->order['shippingAddress']
            ],
            'request_origin' => [
                'ip_address' => $this->customerIpAddress
            ],
            'customer' => [
                'id'    => $this->order['customer']['id'],
                'email' => $this->order['customer']['email']
            ],
            'msg_type'     => 'M',
            'new_customer' => 'N',
            'order_number' => $this->order['ordersNumber'],
            'items'        => $this->order['items']
        ];
    }

    /**
     * @Notes:根据paymentType 获取到body
     * 集成3ds的时候，采用json_encode 不能直接使用json_encode($payload, JSON_FORCE_OBJECT)
     * 由于文档规定
     * @return $this
     * @Author: smile
     * @Date: 2021/6/15
     * @Time: 20:12
     */
    protected function setPayload() : self
    {
        switch ($this->paymentType){
            case 'token':
                $payload = $this->getTokenPayload();
                break;
            case 'integration':
                $payload = $this->getIntegrationPayload();
                $this->payload = json_encode($payload,JSON_UNESCAPED_UNICODE);
                return $this;
            default:
                $payload = $this->get3DXPayload();
        }

        $this->payload = json_encode($payload,JSON_UNESCAPED_UNICODE+JSON_FORCE_OBJECT);

        return $this;
    }

    /**
     * @Notes:支付 其中包含了3ds认证的支付和普通的支付
     *
     * @param array $config
     * @param array $order
     * @param string $paymentType
     * @param string $currencyCode
     * @Author: smile
     * @Date: 2021/6/15
     * @Time: 20:12
     * @return array
     * @throws ApiException
     */
    public function apiPayment(array $config,array $order,string $paymentType,string $currencyCode): array
    {
        $this->order            = $order;
        $this->config           = $config;
        $this->paymentType      = $paymentType;
        $this->currencyCode     = $currencyCode;

        try{
            $response = $this->setPayload()
                ->setSignature($this->payload,false)
                ->setHeaders($this->payload)
                ->call('POST',$this->config['url'],[
                    'headers' => $this->headers,
                    'body'    => $this->payload
                ]);

            if($response->successful()){
                $body = $response->json();

                if (empty($body)){
                    throw new ApiException('支付 返回数据异常'.(string) $response);
                }

                $result = $this->resolveResult($body);

                return compact('result','body');
            }

            $response->throw();
        }catch (\Exception $exception){
            throw new ApiException($exception->getMessage());
        }
    }

    /**
     * @Notes:集成3ds
     *
     * @param array $config
     * @param array $order
     * @param string $paymentType
     * @param string $currencyCode
     * @param string $customerIpAddress
     * @param string $isEuUnionCountry
     * @return array
     * @Author: smile
     * @Date: 2021/6/21
     * @Time: 14:39
     * @throws ApiException
     */
    public function apiIntegration(array $order,array $config,string $paymentType,string $currencyCode,string $customerIpAddress,string $isEuUnionCountry): array
    {
        $this->order             = $order;
        $this->config            = $config;
        $this->paymentType       = $paymentType;
        $this->currencyCode      = $currencyCode;
        $this->isEuUnionCountry  = $isEuUnionCountry;
        $this->customerIpAddress = $customerIpAddress;

        $this->setZeroDollarAuth($currencyCode,$isEuUnionCountry);

        //判断是否能开启3ds认证
        if ($this->zeroDollarAuth != true){
            throw new ApiException('3ds集成 支付的货币类型不满足使用');
        }

        try{
            $response = $this->setPayload()
                ->setSignature($this->payload,false)
                ->setHeaders($this->payload)
                ->call('POST',$this->config['integration_url'],[
                    'headers' => $this->headers,
                    'body'    => $this->payload
                ]);

            if($response->successful()){
                $body = $response->json();

                if (empty($body)){
                    throw new ApiException('3ds集成 返回数据异常'.(string) $response);
                }

                return payeezy_return_success('success',$body['response']);
            }

            $response->throw();
        }catch (\Exception $exception){
            throw new ApiException($exception->getMessage());
        }
    }

    /**
     * @Notes:解析结果
     *
     * @param array $result
     * @return array
     * @Author: smile
     * @Date: 2021/6/17
     * @Time: 15:37
     */
    public function resolveResult(array $result) : array
    {
        if ($result['transaction_status'] != 'approved'){
            $message = 'payeezy error';

            if (isset($result['Error'])){
                $message = $result['Error']['message'] ?? '';
            } else {
                if (isset($result['gateway_resp_code']) && $result['gateway_resp_code'] != '00') {
                    $message = PayeezyEnum::ERROR['gateway_'.$result['gateway_resp_code']] ?? $result['gateway_message'];
                }

                //判断是否存在bank_resp_code返回的参数,由于存在特殊的情况，导致bank_resp_code不存在
                if (isset($result['bank_resp_code']) && !empty($result['bank_resp_code'])) {
                    if (!in_array(intval($result['bank_resp_code']),PayeezyEnum::BANK_SUCCESS_STATUS) || $result['bank_message'] != 'Approved'){
                        $message = PayeezyEnum::ERROR['bank_'.$result['bank_resp_code']] ?? $result['bank_message'];
                    }
                }
            }

            return payeezy_return_error($message);
        }

        return payeezy_return_success('payeezy success');
    }
}