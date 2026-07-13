<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use App\Models\EmployeeLoan;
use App\Models\PayrollRun;
use App\Models\User;
use App\Jobs\GeneratePayrollJob;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PayrollSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::where('status', 'active')->get();
        if ($employees->isEmpty()) {
            return;
        }

        // 1. Create a dummy loan for some employees
        foreach ($employees->random(min(3, $employees->count())) as $emp) {
            EmployeeLoan::create([
                'employee_id' => $emp->id,
                'total_amount' => 5000000,
                'remaining_amount' => 4500000,
                'monthly_installment' => 500000,
            ]);
        }

        // 2. Generate Payroll Runs for the last 3 months
        $hrAdmin = User::whereHas('role', function($q) { $q->where('slug', 'hr-admin'); })->first() ?? User::first();

        for ($i = 3; $i >= 1; $i--) {
            $date = Carbon::now()->subMonths($i);
            
            // Bypass GeneratePayrollJob by manually calling the service? 
            // Or just use the Job synchronously. Since it's queueable, we can call handle() directly if we instantiate it.
            // But wait, the seeder runs synchronously anyway if we configure QUEUE_CONNECTION=sync, 
            // or we can just instantiate the job and call handle.
            $payrollRun = PayrollRun::create([
                'period_month' => $date->month,
                'period_year' => $date->year,
                'status' => 'PAID', // mark as PAID for history
                'created_by' => $hrAdmin->id,
            ]);

            $job = new GeneratePayrollJob($payrollRun->id);
            // Temporarily set status to DRAFT so job can process it
            $payrollRun->status = 'DRAFT';
            $payrollRun->save();
            
            $job->handle(app(\App\Services\PayrollCalculatorService::class));
            
            // Set back to PAID
            $payrollRun->status = 'PAID';
            $payrollRun->save();
        }
    }
}
