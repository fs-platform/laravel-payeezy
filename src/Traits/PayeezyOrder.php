<?php

namespace Smbear\Payeezy\Traits;
use Illuminate\Support\Facades\Http;

trait PayeezyOrder
{
    /**
     * @var array 签名
     */
    public $signature;

    /**
     * @var bool 是否开启3ds认证
     */
    public $zeroDollarAuth;

    /**
     * @Notes:设置是否开启3ds认证
     *
     * @param string $currencyCode
     * @param bool $isEuUnionCountry
     * @return $this
     * @Author: smile
     * @Date: 2021/6/23
     * @Time: 15:27
     */
    public function setZeroDollarAuth(string $currencyCode,bool $isEuUnionCountry):self
    {
        if (is_null($this->zeroDollarAuth)){
            $this->zeroDollarAuth = $isEuUnionCountry == true
                && in_array($currencyCode,config('payeezy.eu_warehouse'));
        }

        return $this;
    }

    /**
     * @Notes:curl 请求
     *
     * @param string $method
     * @param array $parameters
     * @param string $url
     * @return object
     * @throws \Exception
     * @Author: smile
     * @Date: 2021/6/10
     * @Time: 17:07
     */
    public function call(string $method,string $url,array $parameters): object
    {
        try{
            return Http::timeout(60)
                ->send($method,$url,$parameters);
        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
        }
    }

    /**
     * @Notes:设置签名
     *
     * @param string $payload
     * @param bool $type 是否是js调用获取到token
     * @Author: smile
     * @Date: 2021/6/10
     * @Time: 10:57
     * @return self
     */
    public function setSignature(string $payload,$type = true) : self
    {
        if (is_null($this->signature)){
            $nonce = strval(hexdec(bin2hex(openssl_random_pseudo_bytes(4, $cstrong))));
            $timestamp = strval(time() * 1000);

            if ($type == true){
                $data = $this->config['apiKey'] . $nonce . $timestamp .$payload;
                $hmac = hash_hmac('sha256', $data, $this->config['paymentSecret']);
            } else {
                $data = $this->config['apiKey'] . $nonce . $timestamp .$this->config['merchantToken'].$payload;
                $hmac = hash_hmac('sha256', $data, $this->config['apiSecret']);
            }

            $authorization = base64_encode($hmac);

            $this->signature = [
                'authorization' => $authorization,
                'nonce'         => $nonce,
                'timestamp'     => $timestamp
            ];
        }

        return $this;
    }
}