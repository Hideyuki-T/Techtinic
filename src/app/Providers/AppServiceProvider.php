<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\AIEngine;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //AIEngineをシングルトンとしてバインドする。
        $this->app->singleton(AIEngine::class, function ($app) {
            return new AIEngine();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
