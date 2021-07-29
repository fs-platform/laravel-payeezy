<?php

namespace Smbear\Payeezy\Services;

use Smbear\Payeezy\Traits\PayeezyOrder;
use Smbear\Payeezy\Exceptions\ApiException;

class TokenService
{
    use PayeezyOrder;

    /**
     * @var array $config 配置文件
     */
    public $config;

    /**
     * @var array $headers 头信息
     */
    public $headers;

    /**
     * @var string body 信息
     */
    public $payload;

    /**
     * @var string $currencyCode 货币类型
     */
    public $currencyCode;

    /**
     * @var bool $isEuUnionCountry 是否是欧盟国家
     */
    public $isEuUnionCountry;

    /**
     * @Notes:设置headers信息
     *
     * @param string $payload
     * @return $this
     * @Author: smile
     * @Date: 2021/6/10
     * @Time: 16:54
     */
    protected function setHeaders(string $payload): self
    {
        if (is_null($this->headers)){
            $this->headers = [
                'Content-Type'      => 'application/json',
                'Api-Key'           => $this->config['apiKey'],
                'Content-Length'    => strlen($payload),
                'Message-Signature' => strval($this->signature['authorization']),
                'Nonce'             => $this->signature['nonce'],
                'Timestamp'         => $this->signature['timestamp']
            ];
        }

        return $this;
    }

    /**
     * @Notes:设置到body信息，当需要开启3ds认证的时候，zeroDollarAuth设置为false
     *
     * @return $this
     * @Author: smile
     * @Date: 2021/6/10
     * @Time: 16:53
     */
    protected function setPayload() : self
    {
        if (is_null($this->payload)) {
            $payload = [
                'gateway'         => 'PAYEEZY',
                'apiKey'          => $this->config['apiKey'],
                'apiSecret'       => $this->config['apiSecret'],
                'authToken'       => $this->config['merchantToken'],
                'transarmorToken' => $this->config['taToken'],
                'currency'        => $this->currencyCode,
                'zeroDollarAuth'  => !$this->zeroDollarAuth
            ];

            $this->payload = json_encode($payload, JSON_FORCE_OBJECT);
        }

        return $this;
    }

    /**
     * @Notes:api token 请求payeezy token数据
     *
     * @param string $currencyCode
     * @param array $config
     * @param bool $isEuUnionCountry
     * @return array
     * @throws \Exception
     * @Author: smile
     * @Date: 2021/6/10
     * @Time: 17:12
     */
    public function apiToken(array $config,string $currencyCode,bool $isEuUnionCountry) : array
    {
        $this->config            = $config;
        $this->currencyCode      = $currencyCode;
        $this->isEuUnionCountry  = $isEuUnionCountry;

        try{
            $response = $this->setZeroDollarAuth($currencyCode,$isEuUnionCountry)
                ->setPayload()
                ->setSignature($this->payload)
                ->setHeaders($this->payload)
                ->call('POST',$this->config['tokenUrl'],[
                    'headers' => $this->headers,
                    'body'    => $this->payload
                ]);

            if($response->successful()) {
                $clientToken     = $response->header('Client-Token');
                $publicKeyBase64 = $response->body('publicKeyBase64');

                if (empty($clientToken)){
                    $error = 'token 初始化 Client-Token is not defined';
                }

                if (empty($publicKeyBase64)){
                    $error = 'token 初始化 publicKeyBase64 is not defined';
                }

                if ($response->header('Nonce') != $this->signature['nonce']){
                    $error = 'token 初始化 nonce validation failed for nonce ' . $response->header('Nonce');
                }

                if (isset($error)){
                    throw new ApiException($error);
                }

                return payeezy_return_success('success',array_merge([
                    'nonce'            => $this->signature['nonce'],
                    'clientToken'      => $clientToken,
                    'isIntegration3DS' => !$this->zeroDollarAuth
                ],json_decode($publicKeyBase64,true)));
            }

            $response->throw();
        }catch (\Exception $exception){
            throw new ApiException($exception->getMessage());
        }
    }
}