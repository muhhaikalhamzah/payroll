<?php

namespace App\Policies;

use App\Models\EmployeeBankAccount;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EmployeeBankAccountPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-bank-accounts');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, EmployeeBankAccount $employeeBankAccount): bool
    {
        return $user->hasPermission('view-bank-accounts') || $user->id === $employeeBankAccount->employee->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('manage-bank-accounts');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, EmployeeBankAccount $employeeBankAccount): bool
    {
        return $user->hasPermission('manage-bank-accounts') || $user->id === $employeeBankAccount->employee->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, EmployeeBankAccount $employeeBankAccount): bool
    {
        return $user->hasPermission('manage-bank-accounts') || $user->id === $employeeBankAccount->employee->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, EmployeeBankAccount $employeeBankAccount): bool
    {
        return $user->hasPermission('manage-bank-accounts');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, EmployeeBankAccount $employeeBankAccount): bool
    {
        return $user->hasPermission('manage-bank-accounts');
    }
}
