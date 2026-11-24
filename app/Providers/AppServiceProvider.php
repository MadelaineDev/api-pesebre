<?php

namespace App\Providers;

use App\Adapters\MercadoAdapter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // El adaptador se construye con la configuración del .env
        $this->app->singleton(MercadoAdapter::class, fn () => new MercadoAdapter(
            url: config('pesebre.mercado.url'),
            timeoutSegundos: config('pesebre.mercado.timeout'),
            intentos: config('pesebre.mercado.intentos'),
            backoffMs: config('pesebre.mercado.backoff_ms'),
        ));
    }

    public function boot(): void
    {
        //
    }
}
