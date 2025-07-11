<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register'])->name('register');

Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json([
        'status' => true,
        'message' => 'User data retrieved.',
        'data' => $request->user(),
    ]);
});
