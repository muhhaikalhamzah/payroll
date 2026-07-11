<?php

namespace App\Policies;

use App\Models\SalaryComponent;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalaryComponentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-salary-components');
    }

    public function view(User $user, SalaryComponent $salaryComponent): bool
    {
        return $user->hasPermission('view-salary-components');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage-salary-components');
    }

    public function update(User $user, SalaryComponent $salaryComponent): bool
    {
        return $user->hasPermission('manage-salary-components');
    }

    public function delete(User $user, SalaryComponent $salaryComponent): bool
    {
        return $user->hasPermission('manage-salary-components');
    }
}
