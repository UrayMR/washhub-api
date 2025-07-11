<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Response::macro('success', function (string $message, mixed $data = null, int $status = 200) {
            return Response::json([
                'status'  => true,
                'message' => $message,
                'data'    => $data,
            ], $status);
        });

        Response::macro('error', function (string $message, mixed $error = null, int $status = 500) {
            return Response::json([
                'status'  => false,
                'message' => $message,
                'error'   => $error,
            ], $status);
        });
    }
}
