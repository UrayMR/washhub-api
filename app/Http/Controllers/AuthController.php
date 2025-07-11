<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Helpers\ApiResponse;
use Exception;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(AuthRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $user = User::create([
                'id'       => Str::uuid(),
                'name'     => $validated['name'],
                'email'    => $validated['email'],
                'password' => $validated['password'],
                'role'     => $validated['role'] ?? 'admin',
            ]);

            return ApiResponse::success(
                'User registered successfully.',
                new UserResource($user),
                HttpResponse::HTTP_CREATED
            );
        } catch (Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());

            return ApiResponse::error(
                'Failed to register user.',
                $e->getMessage(),
                HttpResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    /**
     * Login user and return token.
     */
    public function login(AuthRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            if (!Auth::attempt($validated)) {
                throw ValidationException::withMessages([
                    'email' => ['The provided credentials are incorrect.'],
                ]);
            }

            $user = User::where('email', $validated['email'])->firstOrFail();

            // Revoke old tokens before creating new one
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return ApiResponse::success(
                'Login successful.',
                [
                    'access_token' => $token,
                    'user'         => new UserResource($user),
                ]
            );
        } catch (ValidationException $e) {
            return ApiResponse::error(
                'Login failed.',
                $e->errors(),
                HttpResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        } catch (Exception $e) {
            Log::error('Login error: ' . $e->getMessage());

            return ApiResponse::error(
                'Something went wrong during login.',
                $e->getMessage(),
                HttpResponse::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
