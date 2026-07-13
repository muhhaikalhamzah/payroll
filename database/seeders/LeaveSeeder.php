<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeaveSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            ['name' => 'Cuti Tahunan', 'max_days' => 12, 'is_carry_forward' => false],
            ['name' => 'Cuti Sakit', 'max_days' => 14, 'is_carry_forward' => false],
            ['name' => 'Cuti Melahirkan', 'max_days' => 90, 'is_carry_forward' => false],
        ];

        foreach ($types as $type) {
            \App\Models\LeaveType::firstOrCreate(['name' => $type['name']], $type);
        }

        // Generate leave balances for this year
        \Illuminate\Support\Facades\Artisan::call('leave:generate-balances');

        // Create dummy leave requests
        $employees = \App\Models\Employee::all();
        if ($employees->isNotEmpty()) {
            $leaveTypes = \App\Models\LeaveType::all();
            
            foreach ($employees as $employee) {
                // Randomly create 0 to 2 leave requests per employee
                $numRequests = rand(0, 2);
                for ($i = 0; $i < $numRequests; $i++) {
                    $leaveType = $leaveTypes->random();
                    $startDate = now()->subDays(rand(1, 60));
                    $endDate = $startDate->copy()->addDays(rand(1, 3));
                    $status = collect(['DRAFT', 'PENDING_MANAGER', 'PENDING_HR', 'APPROVED', 'REJECTED'])->random();

                    $leaveRequest = \App\Models\LeaveRequest::create([
                        'employee_id' => $employee->id,
                        'leave_type_id' => $leaveType->id,
                        'start_date' => $startDate->format('Y-m-d'),
                        'end_date' => $endDate->format('Y-m-d'),
                        'reason' => 'Dummy leave request ' . ($i + 1),
                        'status' => $status
                    ]);

                    // If approved/rejected, add an approval log
                    if (in_array($status, ['APPROVED', 'REJECTED'])) {
                        $leaveRequest->approvals()->create([
                            'approver_id' => \App\Models\User::first()->id ?? 1,
                            'status' => $status,
                            'notes' => 'Auto generated ' . strtolower($status),
                        ]);
                    }
                }
            }
        }
    }
}
