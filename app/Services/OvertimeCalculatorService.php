<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\NationalHoliday;
use Carbon\Carbon;

class OvertimeCalculatorService
{
    /**
     * Calculate total overtime compensation for given hours on a specific date.
     * Based on Depnaker KEP.102/MEN/VI/2004.
     */
    public function calculateOvertimeRate(Employee $employee, Carbon $date, int $hours): float
    {
        if ($hours <= 0) {
            return 0;
        }

        // Determine basic salary
        // Overtime rate base = (1/173) * Basic Salary
        $basicSalary = 0;
        foreach ($employee->salaryComponents as $component) {
            if (strtolower($component->name) === 'basic salary' || strtolower($component->name) === 'gaji pokok') {
                $basicSalary = (float) $component->pivot->amount;
                break;
            }
        }
        $hourlyRate = $basicSalary / 173;

        // Determine schedule type
        $scheduleType = $employee->work_schedule_type_override;
        if (!$scheduleType && $employee->department) {
            $scheduleType = $employee->department->work_schedule_type;
        }
        $scheduleType = $scheduleType ?? '5_days';

        $isRestDay = $this->isRestDay($date, $scheduleType);
        
        $totalMultiplier = 0;

        if (!$isRestDay) {
            // Lembur hari kerja biasa (sama untuk 5 dan 6 hari)
            // Jam ke-1: 1.5x
            // Jam ke-2 dst: 2x
            $totalMultiplier += 1.5; // for the first hour
            if ($hours > 1) {
                $totalMultiplier += 2 * ($hours - 1);
            }
        } else {
            // Lembur di hari istirahat / libur nasional
            if ($scheduleType === '5_days') {
                // Jam ke-1 s/d 8: 2x
                // Jam ke-9: 3x
                // Jam ke-10 dan 11: 4x
                if ($hours <= 8) {
                    $totalMultiplier += 2 * $hours;
                } else if ($hours == 9) {
                    $totalMultiplier += (2 * 8) + 3;
                } else {
                    $excess = $hours - 9;
                    $totalMultiplier += (2 * 8) + 3 + (4 * $excess);
                }
            } else {
                // 6_days
                // Jam ke-1 s/d 7: 2x
                // Jam ke-8: 3x
                // Jam ke-9 dan 10: 4x
                if ($hours <= 7) {
                    $totalMultiplier += 2 * $hours;
                } else if ($hours == 8) {
                    $totalMultiplier += (2 * 7) + 3;
                } else {
                    $excess = $hours - 8;
                    $totalMultiplier += (2 * 7) + 3 + (4 * $excess);
                }
            }
        }

        return $hourlyRate * $totalMultiplier;
    }

    private function isRestDay(Carbon $date, string $scheduleType): bool
    {
        // Check National Holiday
        $isHoliday = NationalHoliday::where('date', $date->toDateString())->exists();
        if ($isHoliday) {
            return true;
        }

        $dayOfWeek = $date->dayOfWeek; // 0 (Sunday) - 6 (Saturday)

        if ($scheduleType === '5_days') {
            // Saturday and Sunday are rest days
            return in_array($dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY]);
        } else {
            // 6_days
            // Only Sunday is rest day
            return $dayOfWeek === Carbon::SUNDAY;
        }
    }
}
