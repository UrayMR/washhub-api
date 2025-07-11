<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Helpers\ApiResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', User::class);

        $users = UserResource::collection(User::all());

        return ApiResponse::success('Users retrieved successfully.', $users, HttpResponse::HTTP_OK);
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        return ApiResponse::success('User retrieved successfully.', new UserResource($user), HttpResponse::HTTP_OK);
    }

    public function store(UserRequest $request)
    {
        $this->authorize('create', User::class);

        $user = User::create($request->validated());

        return ApiResponse::success('User created.', new UserResource($user), HttpResponse::HTTP_CREATED);
    }

    public function update(UserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $user->update($request->validated());

        return ApiResponse::success('User updated.', new UserResource($user), HttpResponse::HTTP_OK);
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        $userDeletedData = [
            'id' => $user->id,
            'name' => $user->name,
            'role' => $user->role,
        ];

        $user->delete();

        return ApiResponse::success('User deleted.', $userDeletedData, HttpResponse::HTTP_OK);
    }
}
