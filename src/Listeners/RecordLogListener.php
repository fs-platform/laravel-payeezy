<?php

namespace Smbear\Payeezy\Listeners;

use Illuminate\Database\Eloquent\Model;
use Smbear\Payeezy\Events\RecordLogEvent;
use Smbear\Payeezy\Exceptions\ConfigException;

class RecordLogListener
{
    /**
     * @Notes:
     *
     * @param RecordLogEvent $event
     * @throws ConfigException
     * @Author: smile
     * @Date: 2021/6/10
     * @Time: 18:10
     */
    public function handle(RecordLogEvent $event)
    {
        $modelName = config('payeezy.model.log_model');

        if (empty($modelName)){
            throw new ConfigException('配置文件 model log_model 参数异常');
        }

        try{
            $model = new $modelName();

            if (!$model instanceof Model){
                throw new ConfigException('配置文件 model log_model 参数异常');
            }

            if (is_object($event->data['exception'])){
                $event->data['exception'] = (string) $event->data['exception']->getMessage();
            }

            $model->create($event->data);

        }catch (\Exception $exception){
            report($exception);

            throw new ConfigException('配置文件 model log_model 参数异常');
        }
    }
}