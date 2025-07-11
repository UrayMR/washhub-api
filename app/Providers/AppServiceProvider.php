<?php

namespace App\Providers;

use App\Exceptions\Handler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ExceptionHandler::class, Handler::class);
    }

    public function boot(): void {}
}
