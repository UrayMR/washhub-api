<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\UserController;

use App\Http\Controllers\OrderController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\TransactionController;

Route::post('/register', [AuthController::class, 'register'])->name('register');
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('users', UserController::class)->names('users');
    Route::apiResource('services', ServiceController::class)->names('services');
    Route::apiResource('customers', CustomerController::class)->names('customers');
    Route::apiResource('orders', OrderController::class)->names('orders');
    Route::apiResource('invoices', InvoiceController::class)->names('invoices');
    Route::apiResource('transactions', TransactionController::class)->names('transactions');
});
