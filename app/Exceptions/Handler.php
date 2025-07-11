<?php

namespace App\Exceptions;

use App\Helpers\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $exception)
    {
        // fallback validation error
        if ($exception instanceof ValidationException) {
            return ApiResponse::error(
                'Validation failed.',
                $exception->errors(),
                HttpResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        // fallback auth error
        if ($exception instanceof AuthenticationException) {
            return ApiResponse::error(
                'Unauthenticated.',
                ['error' => 'Authentication required.'],
                HttpResponse::HTTP_UNAUTHORIZED
            );
        }

        // fallback unauthorized error
        if ($exception instanceof AuthorizationException) {
            return ApiResponse::error(
                'Unauthorized.',
                ['error' => 'You are not allowed to perform this action.'],
                HttpResponse::HTTP_FORBIDDEN
            );
        }

        // fallback response not found
        if ($exception instanceof ModelNotFoundException || $exception instanceof NotFoundHttpException) {
            return ApiResponse::error(
                'Not Found.',
                ['error' => 'The requested resource was not found.'],
                HttpResponse::HTTP_NOT_FOUND
            );
        }

        // fallback default error
        return ApiResponse::error(
            'Server Error.',
            config('app.debug') ? $exception->getMessage() : ['error' => 'Something went wrong.'],
            HttpResponse::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
