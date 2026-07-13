<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmployeeLoan;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;

class EmployeeLoanSeeder extends Seeder
{
    public function run(): void
    {
        $employees = Employee::with('salaryComponents')->inRandomOrder()->limit(4)->get();
        if ($employees->count() < 4) return;

        $financeAdmin = User::whereHas('role', function($q){ $q->where('slug', 'finance-admin'); })->first();
        if (!$financeAdmin) {
            $financeAdmin = User::first();
        }

        // 1. Completed
        $emp1 = $employees[0];
        $amount1 = 3000000;
        $tenor1 = 3;
        EmployeeLoan::create([
            'employee_id' => $emp1->id,
            'request_date' => Carbon::now()->subMonths(4),
            'reason' => 'Emergency medical expenses',
            'total_amount' => $amount1,
            'requested_tenor_months' => $tenor1,
            'monthly_installment' => EmployeeLoan::calculateMonthlyInstallment($amount1, $tenor1),
            'remaining_balance' => 0,
            'status' => 'COMPLETED',
            'approved_by' => $financeAdmin->id,
            'approved_at' => Carbon::now()->subMonths(4)->addDays(1),
            'disbursed_by' => $financeAdmin->id,
            'disbursed_at' => Carbon::now()->subMonths(4)->addDays(2),
        ]);

        // 2. Disbursed/Active (Safe from 50%)
        $emp2 = $employees[1];
        $amount2 = 5000000;
        $tenor2 = 5;
        EmployeeLoan::create([
            'employee_id' => $emp2->id,
            'request_date' => Carbon::now()->subMonths(2),
            'reason' => 'Home repair',
            'total_amount' => $amount2,
            'requested_tenor_months' => $tenor2,
            'monthly_installment' => EmployeeLoan::calculateMonthlyInstallment($amount2, $tenor2),
            'remaining_balance' => 3000000,
            'status' => 'DISBURSED',
            'approved_by' => $financeAdmin->id,
            'approved_at' => Carbon::now()->subMonths(2)->addDays(1),
            'disbursed_by' => $financeAdmin->id,
            'disbursed_at' => Carbon::now()->subMonths(2)->addDays(2),
        ]);

        // 3. Pending
        $emp3 = $employees[2];
        $amount3 = 2000000;
        $tenor3 = 2;
        EmployeeLoan::create([
            'employee_id' => $emp3->id,
            'request_date' => Carbon::now(),
            'reason' => 'School fee',
            'total_amount' => $amount3,
            'requested_tenor_months' => $tenor3,
            'monthly_installment' => EmployeeLoan::calculateMonthlyInstallment($amount3, $tenor3),
            'remaining_balance' => $amount3,
            'status' => 'PENDING_FINANCE',
        ]);

        // 4. Disbursed (Near/Exceed 50% limit)
        // Find basic salary for this employee to set a high loan installment
        $emp4 = $employees[3];
        $basicSalary = $emp4->salaryComponents()->where('type', 'earnings')->sum('amount');
        if ($basicSalary == 0) $basicSalary = 5000000;
        
        $highAmount = $basicSalary * 2; // For 3 months, installment will be ~66% of basic salary
        $tenor4 = 3;
        EmployeeLoan::create([
            'employee_id' => $emp4->id,
            'request_date' => Carbon::now()->subMonths(1),
            'reason' => 'Huge debt consolidation',
            'total_amount' => $highAmount,
            'requested_tenor_months' => $tenor4,
            'monthly_installment' => EmployeeLoan::calculateMonthlyInstallment($highAmount, $tenor4),
            'remaining_balance' => $highAmount,
            'status' => 'DISBURSED',
            'approved_by' => $financeAdmin->id,
            'approved_at' => Carbon::now()->subMonths(1)->addDays(1),
            'disbursed_by' => $financeAdmin->id,
            'disbursed_at' => Carbon::now()->subMonths(1)->addDays(2),
        ]);
    }
}
