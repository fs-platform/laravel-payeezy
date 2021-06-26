<?php

namespace Smbear\Payeezy\Events;

class StorePayStatusEvent
{
    public $data;

    public $ordersId;

    public function __construct(array $data,int $ordersId)
    {
        $this->data = $data;

        $this->ordersId = $ordersId;
    }
}