<?php

namespace Smbear\Payeezy\Services;

use Firebase\JWT\JWT;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Key;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Smbear\Payeezy\Events\RecordLogEvent;

class JwtTokenService
{
    /**
     * @var int $time 当前的时间戳
     */
    public $time;

    /**
     * @var string $local 语言
     */
    public $local;

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
     * @param string $local
     * @Author: smile
     * @Date: 2021/6/22
     * @Time: 19:46
     * @return $this
     */
    public function setParams(array $config,array $order,string $local) : self
    {
        if (is_null($this->order)){
            $this->order = $order;
        }

        if (is_null($this->config)){
            $this->config = $config;
        }

        if (is_null($this->local)){
            $this->local = $local;
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
                event(new RecordLogEvent([
                    'order_id'  => $this->order['ordersId'],
                    'type'      => 4,
                    'exception' => '生成的jwt token 为空'
                ]));

                return payeezy_return_error(payeezy_get_trans('fs_system_busy',$this->local),[]);
            }

            return payeezy_return_success('success',compact('token'));

        }catch (\Exception $exception){
            event(new RecordLogEvent([
                'order_id'  => $this->order['ordersId'],
                'type'      => 4,
                'exception' => $exception
            ]));

            return payeezy_return_error(payeezy_get_trans('fs_system_busy',$this->local),[]);
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
     */
    public function valid(string $token) : array
    {
        try{
            $decoded = JWT::decode($token,$this->config['jwt_apiKey'],['HS256']);

            if (!is_object($decoded)){
                event(new RecordLogEvent([
                    'order_id'  => $this->order['ordersId'],
                    'type'      => 5,
                    'exception' => 'jwt token error'
                ]));

                return payeezy_return_error(payeezy_get_trans('fs_system_busy',$this->local),[]);
            }

            if (!isset($decoded->ReferenceId) || $decoded->ReferenceId != $this->order['ordersId']){
                event(new RecordLogEvent([
                    'order_id'  => $this->order['ordersId'],
                    'type'      => 5,
                    'exception' => 'jwt token ReferenceId error'
                ]));

                return payeezy_return_error(payeezy_get_trans('fs_system_busy',$this->local),[]);
            }

            return payeezy_return_success('success');
        }catch (\Exception $exception){
            event(new RecordLogEvent([
                'order_id'  => $this->order['ordersId'],
                'type'      => 5,
                'exception' => $exception
            ]));

            return payeezy_return_error(payeezy_get_trans('fs_system_busy',$this->local),[]);
        }
    }
}