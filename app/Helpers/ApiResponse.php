<?php

namespace App\Helpers;

use Illuminate\Support\MessageBag;

class ApiResponse
{
  public static function success(string $message, mixed $data = null, int $status = 200)
  {
    return response()->json([
      'status'  => true,
      'message' => $message,
      'data'    => $data,
    ], $status);
  }

  public static function error(string $message, mixed $errors = null, int $status = 500)
  {
    return response()->json([
      'status'  => false,
      'message' => $message,
      'errors'  => is_array($errors) || $errors instanceof MessageBag
        ? $errors
        : ['error' => $errors],
    ], $status);
  }
}
