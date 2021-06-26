<?php

namespace Smbear\Payeezy\Providers;

use Smbear\Payeezy\Events\RecordLogEvent;
use Smbear\Payeezy\Events\StorePayStatusEvent;
use Smbear\Payeezy\Listeners\FileLogListener;
use Smbear\Payeezy\Listeners\RecordLogListener;
use Smbear\Payeezy\Listeners\StorePayStatusListener;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        RecordLogEvent::class => [
            RecordLogListener::class,
            FileLogListener::class
        ],
        StorePayStatusEvent::class => [
            StorePayStatusListener::class
        ]
    ];

    public function boot()
    {
        parent::boot();
    }
}