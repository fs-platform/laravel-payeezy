<?php

namespace Smbear\Payeezy\Services;

use Firebase\JWT\JWT;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Smbear\Payeezy\Exceptions\ApiException;

class JwtTokenService
{
    /**
     * @var int $time 当前的时间戳
     */
    public $time;

    /**
     * @var array $order 订单数据
     */
    public $order;

    /**
     * @var array $config 配置文件
     */
    public $config;

    /**
     * @Notes:设置当前的时间戳
     *
     * @return $this
     * @Author: smile
     * @Date: 2021/6/22
     * @Time: 19:34
     */
    public function setTime() : self
    {
        if (is_null($this->time)){
            $this->time = time();
        }

        return $this;
    }

    /**
     * @Notes:获取到signer
     *
     * @return Sha256
     * @Author: smile
     * @Date: 2021/6/24
     * @Time: 19:36
     */
    public function getSigner(): Sha256
    {
        return new Sha256();
    }

    /**
     * @Notes:获取到key
     *
     * @param string $apiKey
     * @return Key
     * @Author: smile
     * @Date: 2021/6/24
     * @Time: 19:36
     */
    public function getKey(string $apiKey): Key
    {
        return new Key($this->config['jwt_apiKey']);
    }

    /**
     * @Notes:设置参数
     *
     * @param array $order
     * @param array $config
     * @Author: smile
     * @Date: 2021/6/22
     * @Time: 19:46
     * @return $this
     */
    public function setParams(array $config,array $order) : self
    {
        if (is_null($this->order)){
            $this->order = $order;
        }

        if (is_null($this->config)){
            $this->config = $config;
        }
        
        return $this;
    }

    /**
     * @Notes:生成token
     *
     * @return array
     * @Author: smile
     * @Date: 2021/6/22
     * @Time: 19:39
     * @throws ApiException
     */
    public function jwt(): array
    {
        try{
            $build = new Builder();

            $token = $build
                ->setIssuer($this->config['jwt_apiId'])
                ->setId(uniqid(), true)
                ->setIssuedAt($this->time)
                ->setExpiration($this->time + 3600)
                ->set('OrgUnitId', $this->config['jwt_unitId'])
                ->set('Payload', $this->order['parameter'])
                ->set('ObjectifyPayload', true)
                ->set('ReferenceId',$this->order['ordersId'])
                ->sign($this->getSigner(),$this->getKey($this->config['jwt_apiKey']))
                ->getToken();

            $token = (string) $token;

            if (empty($token)){
                throw new ApiException('jwt 生成的token 数据为空');
            }

            return payeezy_return_success('success',compact('token'));

        }catch (\Exception $exception){
            throw new ApiException('生成jwt 异常'.$exception->getMessage());
        }
    }

    /**
     * @Notes:验证生成的token
     *
     * @param string $token
     * @return array
     * @Author: smile
     * @Date: 2021/6/22
     * @Time: 19:42
     * @throws ApiException
     */
    public function valid(string $token) : array
    {
        try{
            $decoded = JWT::decode($token,$this->config['jwt_apiKey'],['HS256']);

            if (!is_object($decoded)){
                throw new ApiException('验证jwt jwt token error');
            }

            if (!isset($decoded->ReferenceId) || $decoded->ReferenceId != $this->order['ordersId']){
                return payeezy_return_error('error');
            }

            return payeezy_return_success('success');
        }catch (\Exception $exception){
            throw new ApiException('验证jwt异常 '.$exception->getMessage());
        }
    }
}