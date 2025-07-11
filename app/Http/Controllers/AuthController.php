<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Helpers\ApiResponse;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(AuthRequest $request)
    {
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
    }

    /**
     * Login user and return token.
     */
    public function login(AuthRequest $request)
    {
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
                'user' => new UserResource($user),
            ]
        );
    }
}
