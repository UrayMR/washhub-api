<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Illuminate\Validation\ValidationException;
use Throwable;


class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ValidationException) {
            return ApiResponse::error('Validation failed.', $exception->errors(), HttpResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        return parent::render($request, $exception);
    }
}
