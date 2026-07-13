<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

class GenerateLeaveBalancesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leave:generate-balances {year?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate annual leave balances for all active employees';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $year = $this->argument('year') ?: date('Y');
        $this->info("Generating leave balances for year $year...");

        $employees = \App\Models\Employee::where('status', 'active')->get();
        $leaveTypes = \App\Models\LeaveType::all();

        foreach ($employees as $employee) {
            foreach ($leaveTypes as $leaveType) {
                // Check if already exists
                $existing = \App\Models\LeaveBalance::where('employee_id', $employee->id)
                    ->where('leave_type_id', $leaveType->id)
                    ->where('year', $year)
                    ->first();

                if (!$existing) {
                    $balance = $leaveType->max_days;

                    // Handle carry forward if needed
                    if ($leaveType->is_carry_forward) {
                        $prevYear = \App\Models\LeaveBalance::where('employee_id', $employee->id)
                            ->where('leave_type_id', $leaveType->id)
                            ->where('year', $year - 1)
                            ->first();
                        if ($prevYear) {
                            $carryForward = max(0, $prevYear->balance - $prevYear->used);
                            $balance += $carryForward;
                        }
                    }

                    \App\Models\LeaveBalance::create([
                        'employee_id' => $employee->id,
                        'leave_type_id' => $leaveType->id,
                        'year' => $year,
                        'balance' => $balance,
                        'used' => 0,
                    ]);
                }
            }
        }

        $this->info('Leave balances generated successfully.');
    }
}
