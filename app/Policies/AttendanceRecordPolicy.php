<?php

namespace App\Policies;

use App\Models\AttendanceRecord;
use App\Models\User;

class AttendanceRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-attendances');
    }

    public function view(User $user, AttendanceRecord $attendanceRecord): bool
    {
        if ($user->hasPermission('manage-attendances')) {
            return true;
        }
        
        return $user->employee?->id === $attendanceRecord->employee_id;
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('manage-attendances');
    }

    public function update(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $user->hasPermission('manage-attendances');
    }

    public function delete(User $user, AttendanceRecord $attendanceRecord): bool
    {
        return $user->hasPermission('manage-attendances');
    }
}
