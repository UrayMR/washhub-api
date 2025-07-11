<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Response;
use Exception;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class AuthController extends Controller
{
    /**
     * Register a new user.
     * 
     * @param AuthRequest $request
     * @return JsonResponse
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

            return Response::success('User registered successfully.', new UserResource($user), HttpFoundationResponse::HTTP_CREATED);
        } catch (Exception $e) {
            Log::error('Registration error: ' . $e->getMessage());
            return Response::error('Failed to register user.', $e->getMessage(), HttpFoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Login user and return token.
     * 
     * @param AuthRequest $request
     * @return JsonResponse
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
            $user->tokens()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            return Response::success('Login successful.', [
                'access_token' => $token,
                'user'         => new UserResource($user),
            ]);
        } catch (ValidationException $e) {
            return Response::error('Login failed.', $e->errors(), HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            Log::error('Login error: ' . $e->getMessage());
            return Response::error('Something went wrong during login.', $e->getMessage(), HttpFoundationResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
