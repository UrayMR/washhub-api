<?php

namespace App\Policies;

use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderItemPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, OrderItem $orderItem): bool
    {
        return $user->role === User::ROLE_SUPER_ADMIN
            || $orderItem->order && $orderItem->order->user_id === $user->id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return in_array($user->role, [User::ROLE_ADMIN, User::ROLE_SUPER_ADMIN]);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, OrderItem $orderItem): bool
    {
        return $user->role === User::ROLE_SUPER_ADMIN
            || $orderItem->order && $orderItem->order->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, OrderItem $orderItem): bool
    {
        return $user->role === User::ROLE_SUPER_ADMIN
            || $orderItem->order->user_id === $user->id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, OrderItem $orderItem): bool
    {
        return $user->role ===  User::ROLE_SUPER_ADMIN;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, OrderItem $orderItem): bool
    {
        return $user->role ===  User::ROLE_SUPER_ADMIN;
    }
}
