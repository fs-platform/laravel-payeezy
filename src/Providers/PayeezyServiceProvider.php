<?php

namespace Smbear\Payeezy\Providers;

use Smbear\Payeezy\Payeezy;
use Illuminate\Support\ServiceProvider;

class PayeezyServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
         $this->publishes([
             __DIR__.'/../../config/payeezy.php' => config_path('payeezy.php'),
         ], 'config');


         $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'payeezy');

         $this->publishes([
             __DIR__.'/../../resources/lang' => resource_path('lang/vendor/payeezy'),
         ], 'translations');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('payeezy',function (){
            return new Payeezy();
        });

        $this->mergeConfigFrom(
            __DIR__.'/../../config/payeezy.php', 'payeezy'
        );
    }
}
