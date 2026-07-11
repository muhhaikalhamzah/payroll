<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-departments');
    }

    public function view(User $user, Department $department): bool
    {
        return $user->hasPermission('view-departments');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage-departments');
    }

    public function update(User $user, Department $department): bool
    {
        return $user->hasPermission('manage-departments');
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->hasPermission('manage-departments');
    }
}
