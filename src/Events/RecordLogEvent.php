<?php

namespace Smbear\Payeezy\Events;

class RecordLogEvent
{
    public $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }
}