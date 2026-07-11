<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-users');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasPermission('view-users');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create-user');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasPermission('update-user');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasPermission('delete-user');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->hasPermission('delete-user');
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasPermission('delete-user');
    }
}
