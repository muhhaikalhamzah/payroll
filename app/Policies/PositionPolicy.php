<?php

namespace App\Policies;

use App\Models\Position;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PositionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-positions');
    }

    public function view(User $user, Position $position): bool
    {
        return $user->hasPermission('view-positions');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage-positions');
    }

    public function update(User $user, Position $position): bool
    {
        return $user->hasPermission('manage-positions');
    }

    public function delete(User $user, Position $position): bool
    {
        return $user->hasPermission('manage-positions');
    }
}
