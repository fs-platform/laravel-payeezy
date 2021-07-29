<?php

namespace Smbear\Payeezy;

use Illuminate\Support\Facades\Log;
use Smbear\Payeezy\Traits\PayeezyConfig;
use Smbear\Payeezy\Services\TokenService;
use Smbear\Payeezy\Services\PaymentService;
use Smbear\Payeezy\Services\JwtTokenService;
use Smbear\Payeezy\Exceptions\OrderException;
use Smbear\Payeezy\Exceptions\MethodException;
use Smbear\Payeezy\Exceptions\ParameterException;

class Payeezy
{
    use PayeezyConfig;

    /**
     * @var string $local 语言包
     */
    public $local;

    /**
     * @var array $order 订单信息
     */
    public $order;

    /**
     * @var string $paymentType 付款类型
     */
    public $paymentType;

    /**
     * @var object TokenService token的服务
     */
    public $tokenService;

    /**
     * @var string currencyCode 货币类型
     */
    public $currencyCode;

    /**
     * @var object PaymentService 支付的服务
     */
    public $paymentService;

    /**
     * @var object $jwtTokenService jwt token的服务
     */
    public $jwtTokenService;

    /**
     * @var bool $isEuUnionCountry 是否是欧盟国家
     */
    public $isEuUnionCountry;

    /**
     * @var string $customerIpAddress 客户请求的ip地址
     */
    public $customerIpAddress;

    public function __construct()
    {
        $this->setEnvironment();

        $this->tokenService = new TokenService();

        $this->paymentService = new PaymentService();

        $this->jwtTokenService = new JwtTokenService();
    }

    /**
     * @Notes:设置本地的语言包
     *
     * @param string $local
     * @return $this
     * @Author: smile
     * @Date: 2021/6/10
     * @Time: 17:31
     * @throws ParameterException
     */
    public function setLocal(string $local) : self
    {
        if (empty($local)){
            throw new ParameterException(__FUNCTION__.' 参数异常');
        }

        $this->local = $local;

        return $this;
    }

    /**
     * @Notes:设置货币类型
     *
     * @param string $currencyCode
     * @Author: smile
     * @Date: 2021/6/15
     * @Time: 20:17
     * @return $this
     * @throws ParameterException
     */
    public function setCurrencyCode(string $currencyCode) : self
    {
        if (empty($currencyCode)){
            throw new ParameterException(__FUNCTION__.' 参数异常');
        }

        $this->currencyCode = $currencyCode;

        return $this;
    }

    /**
     * @Notes:设置是否是欧盟国家
     *
     * @param $isEuUnionCountry
     * @return $this
     * @Author: smile
     * @Date: 2021/6/22
     * @Time: 17:38
     */
    public function setEuUnionCountry(bool $isEuUnionCountry) : self
    {
        $this->isEuUnionCountry = $isEuUnionCountry;

        return $this;
    }

    /**
     * @Notes: 设置order信息
     *
     * @param array $order
     * @return $this
     * @Author: smile
     * @Date: 2021/6/16
     * @Time: 10:41
     * @throws ParameterException
     */
    public function setOrder(array $order) : self
    {
        if (empty($order)){
            throw new ParameterException(__FUNCTION__.' 参数异常');
        }

        $this->order = $order;

        return $this;
    }

    /**
     * @Notes:设置付款方式
     *
     * @param string $paymentType
     * @return $this
     * @Author: smile
     * @Date: 2021/6/16
     * @Time: 20:10
     * @throws ParameterException
     */
    public function setPaymentType(string $paymentType) : self
    {
        if (empty($paymentType)){
            throw new ParameterException(__FUNCTION__.' 参数异常');
        }

        $this->paymentType = $paymentType;

        return $this;
    }

    /**
     * @Notes:设置请求的用户的ip地址
     *
     * @param string $ipAddress
     * @return $this
     * @Author: smile
     * @Date: 2021/6/20
     * @Time: 15:31
     * @throws ParameterException
     */
    public function setCustomerIpAddress(string $ipAddress) : self
    {
        if (empty($ipAddress)){
            throw new ParameterException(__FUNCTION__.' 参数异常');
        }

        $this->customerIpAddress = $ipAddress;

        return $this;
    }

    /**
     * @Notes: 验证方法是否被使用
     *
     * @param array $parameters
     * @throws MethodException
     * @Author: smile
     * @Date: 2021/6/16
     * @Time: 10:47
     */
    protected function checkMethod($parameters = [])
    {
        foreach ($parameters as $method => $attribute){
            if (is_null($this->$attribute)){
                throw new MethodException($method .' is not call');
            }
        }
    }

    /**
     * @Notes:核对order数据是否均被设置
     *
     * @param array $parameters
     * @param array $order
     * @throws OrderException
     * @Author: smile
     * @Date: 2021/6/16
     * @Time: 10:55
     */
    protected function checkOrder(array $parameters = [],array $order)
    {
        if (empty($order) || !is_array($order)){
            throw new OrderException('order is error');
        }

        array_map(function ($item) use ($order){
            if (strpos($item,'.') !== false){
                $array = $order;

                foreach (explode('.',$item) as $segment){
                    $array = $array[$segment] ?? null;

                    if (is_null($array)) {
                        continue;
                    }
                }

                $option = $array;
            } else {
                $option = $order[$item] ?? null;
            }

            if (empty($option)){
                throw new OrderException('order '.$item . ' is not defined or error');
            }

        },$parameters);
    }

    /**
     * @Notes:获取到第三方的token
     *
     * @return mixed
     * @throws Exceptions\ConfigException
     * @throws \Exception
     * @Author: smile
     * @Date: 2021/6/10
     * @Time: 17:05
     */
    public function token()
    {
        $this->getConfig([
            'apiKey',
            'taToken',
            'tokenUrl',
            'merchantToken',
            'paymentSecret',
        ]);

        $this->checkMethod([
            'setCurrencyCode'   => 'currencyCode',
            'setEuUnionCountry' => 'isEuUnionCountry'
        ]);

        return $this->tokenService
            ->apiToken($this->config,$this->currencyCode,$this->isEuUnionCountry);
    }

    /**
     * @Notes: 3Ds集成
     *
     * @throws Exceptions\ConfigException
     * @throws MethodException
     * @throws OrderException|Exceptions\ApiException
     * @Author: smile
     * @Date: 2021/6/20
     * @Time: 18:45
     */
    public function integration(): array
    {
        $this->getConfig([
            'apiKey',
            'apiSecret',
            'merchantToken',
            'integration_url'
        ]);

        $this->checkMethod([
            'setOrder'             => 'order',
            'setPaymentType'       => 'paymentType',
            'setCurrencyCode'      => 'currencyCode',
            'setEuUnionCountry'    => 'isEuUnionCountry',
            'setCustomerIpAddress' => 'customerIpAddress'
        ]);

        $this->checkOrder([
            'ordersNumber',
            'ordersId',
            'amount',
            'card.type',
            'card.value',
            'card.cardHolderName',
            'card.exp_date',
            'billingAddress',
            'customer.id',
            'customer.email',
            'items',
            'shippingAddress'
        ],$this->order);

        return $this->paymentService
            ->apiIntegration($this->order,$this->config,$this->paymentType,$this->currencyCode,$this->customerIpAddress,$this->isEuUnionCountry);
    }

    /**
     * @Notes:支付/3ds支付
     *
     * @Author: smile
     * @Date: 2021/6/10
     * @Time: 19:09
     * @throws Exceptions\ConfigException
     * @throws MethodException
     * @throws OrderException|Exceptions\ApiException
     */
    public function payment(): array
    {
        $this->getConfig([
            'apiKey',
            'apiSecret',
            'merchantToken',
            'url'
        ]);

        $this->checkMethod([
            'setOrder'          => 'order',
            'setPaymentType'    => 'paymentType',
            'setCurrencyCode'   => 'currencyCode',
        ]);

        $checkOrderParameter = [
            'ordersNumber',
            'ordersId',
            'card.type',
            'card.value',
            'card.cardHolderName',
            'card.exp_date',
            'billingAddress',
            'amount'
        ];

        //通过配置文件判断是否开启3ds付款
        if (config('payeezy.3ds_status') != true){
            $this->paymentType = 'token';
        }

        if ($this->paymentType == '3DS'){
            array_push($checkOrderParameter,'3ds.ExtendedData.CAVV');
            array_push($checkOrderParameter,'ds_transaction_id');
        };

        $this->checkOrder($checkOrderParameter,$this->order);

        return $this->paymentService
            ->apiPayment($this->config,$this->order,$this->paymentType,$this->currencyCode);
    }

    /**
     * @Notes:初始化jwt,返回给前端token
     *
     * @return array
     * @throws Exceptions\ConfigException
     * @throws MethodException
     * @throws OrderException|Exceptions\ApiException
     * @Author: smile
     * @Date: 2021/6/22
     * @Time: 15:20
     */
    public function jwt() : array
    {
        $this->getConfig([
            'jwt_apiKey',
            'jwt_apiId',
            'jwt_unitId'
        ]);

        $this->checkMethod([
            'setOrder' => 'order',
        ]);

        $this->checkOrder([
            'ordersId',
            'parameter.OrderDetails.orderNumber',
            'parameter.OrderDetails.Amount',
            'parameter.OrderDetails.currencyCode',
        ],$this->order);

        return $this->jwtTokenService
            ->setParams($this->config,$this->order)
            ->setTime()
            ->jwt();
    }

    /**
     * @Notes:检验jwt token 是否有效
     *
     * @param string $token
     * @return array
     * @throws Exceptions\ConfigException
     * @throws MethodException
     * @throws OrderException|Exceptions\ApiException
     * @Author: smile
     * @Date: 2021/6/23
     * @Time: 10:14
     */
    public function valid(string $token) : array
    {
        $this->getConfig([
            'jwt_apiKey',
        ]);

        $this->checkMethod([
            'setOrder'  => 'order',
        ]);

        $this->checkOrder([
            'ordersId',
        ],$this->order);

        return $this->jwtTokenService
            ->setParams($this->config,$this->order)
            ->valid($token);
    }
}