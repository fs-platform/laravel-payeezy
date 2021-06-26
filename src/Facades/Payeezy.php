<?php

namespace Smbear\Payeezy\Facades;

use Illuminate\Support\Facades\Facade;

class Payeezy extends Facade
{
    protected static function getFacadeAccessor() : string
    {
        return 'payeezy';
    }
}