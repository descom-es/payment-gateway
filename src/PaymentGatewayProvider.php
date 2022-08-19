<?php

namespace Descom\Payment;

use Illuminate\Support\ServiceProvider;

class PaymentGatewayProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'descommarket_payment');
    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
              __DIR__.'/../config/config.php' => config_path('descommarket_payment.php'),
            ], 'config');
        }
    }
}
