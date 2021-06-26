<?php

namespace Smbear\Payeezy;

use Illuminate\Support\Facades\Log;
use Smbear\Payeezy\Enums\PayeezyEnum;
use Smbear\Payeezy\Traits\PayeezyConfig;
use Smbear\Payeezy\Services\TokenService;
use Smbear\Payeezy\Services\PaymentService;
use Smbear\Payeezy\Services\JwtTokenService;
use Smbear\Payeezy\Exceptions\OrderException;
use Smbear\Payeezy\Exceptions\MethodException;

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
     * @var string currencyCode 货币类型
     */
    public $currencyCode;

    /**
     * @var string $paymentType 付款类型
     */
    public $paymentType;

    /**
     * @var bool $isEuUnionCountry 是否是欧盟国家
     */
    public $isEuUnionCountry;

    /**
     * @var string $customerIpAddress 客户请求的ip地址
     */
    public $customerIpAddress;

    /**
     * @var object TokenService token的服务
     */
    public $tokenService;

    /**
     * @var object $jwtTokenService jwt token的服务
     */
    public $jwtTokenService;

    /**
     * @var object PaymentService 支付的服务
     */
    public $paymentService;

    public function __construct()
    {
        //初始化环境
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
     */
    public function setLocal(string $local = PayeezyEnum::LOCAL) : self
    {
        if (is_null($this->local)){
            $local = $local ?: PayeezyEnum::LOCAL;

            $this->local = $local;
        }

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
     */
    public function setCurrencyCode(string $currencyCode = PayeezyEnum::CURRENCY_CODE) : self
    {
        if (is_null($this->currencyCode)) {
            $currencyCode = $currencyCode ?: PayeezyEnum::CURRENCY_CODE;

            $this->currencyCode = $currencyCode;
        }

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
    public function setEuUnionCountry(bool $isEuUnionCountry = PayeezyEnum::IS_EN_UNION_COUNTRY) : self
    {
        if (is_null($this->isEuUnionCountry)){
            $isEuUnionCountry = $isEuUnionCountry ?: PayeezyEnum::IS_EN_UNION_COUNTRY;

            $this->isEuUnionCountry = $isEuUnionCountry;
        }

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
     */
    public function setOrder(array $order = PayeezyEnum::ORDER) : self
    {
        if (!empty($order)){
            $order = $order ?: PayeezyEnum::ORDER;

            $this->order = $order;
        }

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
     */
    public function setPaymentType(string $paymentType = PayeezyEnum::PAYMENT_TYPE) : self
    {
        if (is_null($this->paymentType)){
            $paymentType = $paymentType ?: PayeezyEnum::PAYMENT_TYPE;

            $this->paymentType = $paymentType;
        }

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
     */
    public function setCustomerIpAddress(string $ipAddress = PayeezyEnum::CUSTOMER_ADDRESS) : self
    {
        if (is_null($this->customerIpAddress)){
            $ipAddress = $ipAddress ?: PayeezyEnum::CUSTOMER_ADDRESS;

            $this->customerIpAddress = $ipAddress;
        }

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
                Log::channel(config('payeezy.channel') ?: 'local')
                    ->info($method. ' 方法未被调用');

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
            //判断字符串中是否存在.获取到嵌套值
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
                Log::channel(config('payeezy.channel') ?: 'local')
                    ->info('order 参数中'.$item. ' 没有或为空');

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
        $this->setConfig([
            'apiKey',
            'paymentSecret',
            'merchantToken',
            'taToken',
            'tokenUrl'
        ]);

        $this->checkMethod([
            'setLocal'          => 'local',
            'setCurrencyCode'   => 'currencyCode',
            'setOrder'          => 'order',
            'setEuUnionCountry' => 'isEuUnionCountry'
        ]);

        $this->checkOrder([
            'ordersId'
        ],$this->order);

        return $this->tokenService
            ->apiToken($this->order,$this->config,$this->local,$this->currencyCode,$this->isEuUnionCountry);
    }

    /**
     * @Notes: 3Ds集成
     *
     * @throws Exceptions\ConfigException
     * @throws MethodException
     * @throws OrderException
     * @Author: smile
     * @Date: 2021/6/20
     * @Time: 18:45
     */
    public function integration(): array
    {
        $this->setConfig([
            'apiKey',
            'apiSecret',
            'merchantToken',
            'integration_url'
        ]);

        $this->checkMethod([
            'setLocal'             => 'local',
            'setCurrencyCode'      => 'currencyCode',
            'setOrder'             => 'order',
            'setPaymentType'       => 'paymentType',
            'setCustomerIpAddress' => 'customerIpAddress',
            'setEuUnionCountry'    => 'isEuUnionCountry'
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
            ->apiIntegration($this->order,$this->config,$this->paymentType,$this->currencyCode,$this->local,$this->customerIpAddress,$this->isEuUnionCountry);
    }

    /**
     * @Notes:支付/3ds支付
     *
     * @Author: smile
     * @Date: 2021/6/10
     * @Time: 19:09
     * @throws Exceptions\ConfigException
     * @throws MethodException
     * @throws OrderException
     */
    public function payment(): array
    {
        $this->setConfig([
            'apiKey',
            'apiSecret',
            'merchantToken',
            'url'
        ]);

        $this->checkMethod([
            'setLocal'          => 'local',
            'setCurrencyCode'   => 'currencyCode',
            'setOrder'          => 'order',
            'setPaymentType'    => 'paymentType'
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
            array_push($checkOrderParameter,'3ds');
        };

        $this->checkOrder($checkOrderParameter,$this->order);

        return $this->paymentService
            ->apiPayment($this->config,$this->order,$this->paymentType,$this->currencyCode,$this->local);
    }

    /**
     * @Notes:初始化jwt,返回给前端token
     *
     * @return array
     * @throws Exceptions\ConfigException
     * @throws MethodException
     * @throws OrderException
     * @Author: smile
     * @Date: 2021/6/22
     * @Time: 15:20
     */
    public function jwt() : array
    {
        $this->setConfig([
            'jwt_apiKey',
            'jwt_apiId',
            'jwt_unitId'
        ]);

        $this->checkMethod([
            'setLocal' => 'local',
            'setOrder' => 'order',
        ]);

        $this->checkOrder([
            'ordersId',
            'parameter.OrderDetails.orderNumber',
            'parameter.OrderDetails.Amount',
            'parameter.OrderDetails.currencyCode',
        ],$this->order);

        return $this->jwtTokenService
            ->setParams($this->config,$this->order,$this->local)
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
     * @throws OrderException
     * @Author: smile
     * @Date: 2021/6/23
     * @Time: 10:14
     */
    public function valid(string $token) : array
    {
        $this->setConfig([
            'jwt_apiKey',
        ]);

        $this->checkMethod([
            'setLocal'  => 'local',
            'setOrder'  => 'order',
        ]);

        $this->checkOrder([
            'ordersId',
        ],$this->order);

        return $this->jwtTokenService
            ->setParams($this->config,$this->order,$this->local)
            ->valid($token);
    }
}