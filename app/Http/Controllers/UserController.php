<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use App\Helpers\ApiResponse;
use Exception;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        try {
            $this->authorize('viewAny', User::class);

            $users = UserResource::collection(User::all());

            return ApiResponse::success('Users retrieved successfully.', $users, HttpResponse::HTTP_OK);
        } catch (Exception $e) {
            Log::error('User index error: ' . $e->getMessage());

            return ApiResponse::error('Failed to retrieve users.', $e->getMessage(), HttpResponse::HTTP_FORBIDDEN);
        }
    }

    public function show(User $user)
    {
        try {
            $this->authorize('view', $user);

            return ApiResponse::success('User retrieved successfully.', new UserResource($user), HttpResponse::HTTP_OK);
        } catch (Exception $e) {
            Log::error('User show error: ' . $e->getMessage());

            return ApiResponse::error('Failed to retrieve user.', $e->getMessage(), HttpResponse::HTTP_FORBIDDEN);
        }
    }

    public function store(UserRequest $request)
    {
        try {
            $this->authorize('create', User::class);

            $user = User::create($request->validated());

            return ApiResponse::success('User created.', new UserResource($user), HttpResponse::HTTP_CREATED);
        } catch (Exception $e) {
            Log::error('User store error: ' . $e->getMessage());

            return ApiResponse::error('Failed to create user.', $e->getMessage(), HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(UserRequest $request, User $user)
    {
        try {
            $this->authorize('update', $user);

            $user->update($request->validated());

            return ApiResponse::success('User updated.', new UserResource($user), HttpResponse::HTTP_OK);
        } catch (Exception $e) {
            Log::error('User update error: ' . $e->getMessage());

            return ApiResponse::error('Failed to update user.', $e->getMessage(), HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy(User $user)
    {
        try {
            $this->authorize('delete', $user);

            $userDataTemp = [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
            ];

            $user->delete();

            return ApiResponse::success('User deleted.', $userDataTemp, HttpResponse::HTTP_OK);
        } catch (Exception $e) {
            Log::error('User delete error: ' . $e->getMessage());

            return ApiResponse::error('Failed to delete user.', $e->getMessage(), HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
