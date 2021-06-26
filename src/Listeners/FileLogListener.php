<?php

namespace Smbear\Payeezy\Listeners;

use Illuminate\Support\Facades\Log;
use Smbear\Payeezy\Events\RecordLogEvent;

class FileLogListener
{
    /**
     * @Notes:
     *
     * @param RecordLogEvent $event
     * @Author: smile
     * @Date: 2021/6/10
     * @Time: 18:10
     */
    public function handle(RecordLogEvent $event)
    {
        if (!empty(config('payeezy.channel'))){

            try{
                if (is_string($event->data['exception'])){
                    Log::channel(config('payeezy.channel'))
                        ->info($event->data['exception']);
                }

                if (is_object($event->data['exception']) && $event->data['exception'] instanceof \Exception){
                    Log::channel(config('payeezy.channel'))
                        ->info($event->data['exception']->getMessage(),(array) $event->data['exception']);
                }
            }catch (\Exception $exception){
                report($exception);
            }
        }
    }
}