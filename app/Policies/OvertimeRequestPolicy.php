<?php

namespace App\Policies;

use App\Models\OvertimeRequest;
use App\Models\User;

class OvertimeRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-overtime-requests');
    }

    public function view(User $user, OvertimeRequest $overtimeRequest): bool
    {
        if ($user->hasPermission('approve-overtime-requests') || $user->hasPermission('manage-overtime-requests')) {
            return true;
        }

        return $user->employee?->id === $overtimeRequest->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('submit-overtime-requests') || $user->hasPermission('manage-overtime-requests');
    }

    public function update(User $user, OvertimeRequest $overtimeRequest): bool
    {
        if ($user->hasPermission('manage-overtime-requests')) {
            return true;
        }

        if ($user->hasPermission('approve-overtime-requests') && in_array($overtimeRequest->status, ['PENDING_MANAGER', 'APPROVED', 'REJECTED'])) {
            return true;
        }

        // Owner can update if status is still DRAFT
        if ($user->employee?->id === $overtimeRequest->employee_id && $overtimeRequest->status === 'DRAFT') {
            return true;
        }

        return false;
    }

    public function delete(User $user, OvertimeRequest $overtimeRequest): bool
    {
        if ($user->hasPermission('manage-overtime-requests')) {
            return true;
        }

        return $user->employee?->id === $overtimeRequest->employee_id && $overtimeRequest->status === 'DRAFT';
    }
}
