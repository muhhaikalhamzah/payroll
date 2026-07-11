<?php

namespace App\Policies;

use App\Models\SalaryStructure;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalaryStructurePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-salary-structures');
    }

    public function view(User $user, SalaryStructure $salaryStructure): bool
    {
        return $user->hasPermission('view-salary-structures');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage-salary-structures');
    }

    public function update(User $user, SalaryStructure $salaryStructure): bool
    {
        return $user->hasPermission('manage-salary-structures');
    }

    public function delete(User $user, SalaryStructure $salaryStructure): bool
    {
        return $user->hasPermission('manage-salary-structures');
    }
}
