<?php

namespace Smbear\Payeezy\Listeners;

use Illuminate\Database\Eloquent\Model;
use Smbear\Payeezy\Events\StorePayStatusEvent;
use Smbear\Payeezy\Exceptions\ConfigException;

class StorePayStatusListener
{
    /**
     * @Notes:
     *
     * @param StorePayStatusEvent $event
     * @throws ConfigException
     * @Author: smile
     * @Date: 2021/6/10
     * @Time: 18:10
     */
    public function handle(StorePayStatusEvent $event)
    {
        $modelName = config('payeezy.model.status_model');

        if (empty($modelName)){
            throw new ConfigException('配置文件 model status_model 参数异常');
        }

        try{
            $model = new $modelName();

            if (!$model instanceof Model){
                throw new ConfigException('配置文件 model status_model 参数异常');
            }

            $data = $this->format($event->data,$event->ordersId);

            $model->create($data);

        }catch (\Exception $exception){
            report($exception);

            throw new ConfigException('配置文件 model status_model 参数异常');
        }
    }

    /**
     * @Notes:格式化数据
     *
     * @param array $data
     * @param int $ordersId
     * @return array
     * @Author: smile
     * @Date: 2021/6/17
     * @Time: 15:16
     */
    public function format(array $data,int $ordersId) : array
    {
        return [
            'orders_id'   => $ordersId,
            'status_id'   => $data['bank_resp_code'] ?? '',
            'imformation' => json_encode($data, JSON_FORCE_OBJECT),
            'description' => $data['transaction_status'] ?? '',
            'datetime'    => date("Y-m-d H:i:s"),
            'payment_id'  => $data['transaction_tag'] ?? '',
            'type'        => 2
        ];
    }
}