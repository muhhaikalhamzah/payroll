<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PayrollCalculatorService
{
    /**
     * Calculate payroll for a given employee and period.
     *
     * @param Employee $employee
     * @param int $month
     * @param int $year
     * @return array
     */
    public function calculate(Employee $employee, int $month, int $year): array
    {
        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        // 1. Get Base Salary and Allowances/Deductions from Salary Components
        $basicSalary = 0;
        $allowances = [];
        $deductions = [];

        foreach ($employee->salaryComponents as $component) {
            if (strtolower($component->name) === 'basic salary' || strtolower($component->name) === 'gaji pokok') {
                $basicSalary = (float) $component->pivot->amount;
            } elseif ($component->type === 'allowance') {
                $allowances[] = [
                    'name' => $component->name,
                    'amount' => (float) $component->pivot->amount,
                ];
            } elseif ($component->type === 'deduction') {
                $deductions[] = [
                    'name' => $component->name,
                    'amount' => (float) $component->pivot->amount,
                ];
            }
        }

        // 2. Prorata if hire date is within this month
        $hireDate = Carbon::parse($employee->hire_date);
        $totalWorkingDaysInMonth = 22; // Assumption per month
        if ($hireDate->year === $year && $hireDate->month === $month) {
            $actualWorkingDays = $endDate->diffInDaysFiltered(function (Carbon $date) {
                return !$date->isWeekend();
            }, $hireDate);
            $prorataFactor = min(1, $actualWorkingDays / $totalWorkingDaysInMonth);
            $basicSalary = $basicSalary * $prorataFactor;
        }

        // 3. Overtime calculation
        $overtimeRequests = \App\Models\OvertimeRequest::where('employee_id', $employee->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', 'APPROVED')
            ->get();

        $overtimePay = 0;
        $overtimeService = new \App\Services\OvertimeCalculatorService();
        
        foreach ($overtimeRequests as $request) {
            $hours = floor($request->duration_minutes / 60);
            if ($hours > 0) {
                $overtimePay += $overtimeService->calculateOvertimeRate($employee, Carbon::parse($request->date), $hours);
            }
        }
        
        if ($overtimePay > 0) {
            $allowances[] = [
                'name' => 'Uang Lembur',
                'amount' => $overtimePay,
            ];
        }

        // 4. Employee Loans (deduction)
        $loans = $employee->employeeLoans()->where('remaining_balance', '>', 0)->where('status', 'DISBURSED')->get();
        $loanDeductions = [];
        $totalLoanDeductions = 0;
        foreach ($loans as $loan) {
            $installment = min($loan->monthly_installment, $loan->remaining_balance);
            if ($installment > 0) {
                $loanDeductions[] = [
                    'name' => 'Loan Installment',
                    'amount' => $installment,
                    'loan_id' => $loan->id, // For tracking
                ];
                $totalLoanDeductions += $installment;
            }
        }

        // 5. Total Gross Pay
        $totalAllowances = array_sum(array_column($allowances, 'amount'));
        $grossPay = $basicSalary + $totalAllowances;

        // 6. BPJS Deductions & Allowances
        $bpjsConfig = config('payroll.bpjs', []);
        
        // Capped wages for BPJS
        $kesWage = min($basicSalary, $bpjsConfig['kesehatan']['max_wage'] ?? 12000000);
        $jpWage = min($basicSalary, $bpjsConfig['jp']['max_wage'] ?? 10042300);

        // Company paid (Allowances added to Bruto)
        $jkkCompany = $basicSalary * ($bpjsConfig['jkk']['company_pct'] ?? 0.0024);
        $jkmCompany = $basicSalary * ($bpjsConfig['jkm']['company_pct'] ?? 0.003);
        $kesCompany = $kesWage * ($bpjsConfig['kesehatan']['company_pct'] ?? 0.04);
        
        // Add to allowances
        $allowances[] = ['name' => 'Tunjangan BPJS JKK', 'amount' => $jkkCompany];
        $allowances[] = ['name' => 'Tunjangan BPJS JKM', 'amount' => $jkmCompany];
        $allowances[] = ['name' => 'Tunjangan BPJS Kesehatan', 'amount' => $kesCompany];

        // Re-calculate Gross Pay with new BPJS allowances
        $totalAllowances = array_sum(array_column($allowances, 'amount'));
        $grossPay = $basicSalary + $totalAllowances;

        // Employee paid (Deductions)
        $jhtEmployee = $basicSalary * ($bpjsConfig['jht']['employee_pct'] ?? 0.02);
        $jpEmployee  = $jpWage * ($bpjsConfig['jp']['employee_pct'] ?? 0.01);
        $kesEmployee = $kesWage * ($bpjsConfig['kesehatan']['employee_pct'] ?? 0.01);

        $bpjsRecord = [
            'jht_amount' => $jhtEmployee,
            'jp_amount' => $jpEmployee,
            'jkk_amount' => $jkkCompany,
            'jkm_amount' => $jkmCompany,
            'kesehatan_amount' => $kesEmployee,
        ];

        $deductions[] = ['name' => 'BPJS JHT (2%)', 'amount' => $jhtEmployee];
        $deductions[] = ['name' => 'BPJS JP (1%)', 'amount' => $jpEmployee];
        $deductions[] = ['name' => 'BPJS Kesehatan (1%)', 'amount' => $kesEmployee];

        // 7. PPh 21 (TER logic)
        $taxStatus = $employee->tax_status ?? 'TK/0';
        $terCategory = config("payroll.ter_categories.{$taxStatus}", 'A');
        $terRates = config("payroll.ter_rates.{$terCategory}", []);

        $pph21Rate = 0;
        foreach ($terRates as $rateTier) {
            if ($grossPay <= $rateTier[0]) {
                $pph21Rate = $rateTier[1];
                break;
            }
        }
        
        $pph21Amount = max(0, $grossPay * $pph21Rate);

        $taxRecord = [
            'ter_category' => $terCategory,
            'bruto_amount' => $grossPay,
            'pph21_amount' => $pph21Amount,
        ];
        
        if ($pph21Amount > 0) {
            $deductions[] = ['name' => 'PPh 21', 'amount' => $pph21Amount];
        }

        // 8. Calculate total mandatory deductions
        $totalMandatoryDeductions = array_sum(array_column($deductions, 'amount'));

        // 9. Apply Loan Deductions with 50% Gross Pay Limit
        $maxTotalDeductions = $grossPay * 0.5;
        $needsIntervention = false;
        $interventionReason = null;

        $availableForLoan = $maxTotalDeductions - $totalMandatoryDeductions;
        if ($availableForLoan < 0) {
            $availableForLoan = 0; // cannot deduct any loan
        }

        foreach ($loanDeductions as &$ld) {
            if ($ld['amount'] > $availableForLoan) {
                // We have to reduce the loan deduction
                $originalAmount = $ld['amount'];
                $ld['amount'] = $availableForLoan;
                $needsIntervention = true;
                $interventionReason = "Loan deduction was reduced from " . number_format($originalAmount) . " to " . number_format($availableForLoan) . " because total deductions exceed 50% of gross pay.";
                $availableForLoan = 0; // exhausted
            } else {
                $availableForLoan -= $ld['amount'];
            }

            if ($ld['amount'] > 0) {
                $deductions[] = $ld;
            }
        }

        // 10. Net Pay
        $totalDeductions = array_sum(array_column($deductions, 'amount'));
        $netPay = $grossPay - $totalDeductions;

        return [
            'basic_salary' => $basicSalary,
            'total_allowances' => $totalAllowances,
            'total_deductions' => $totalDeductions,
            'net_pay' => $netPay,
            'allowance_components' => $allowances,
            'deduction_components' => $deductions,
            'tax_record' => $taxRecord,
            'bpjs_record' => $bpjsRecord,
            'needs_intervention' => $needsIntervention,
            'intervention_reason' => $interventionReason,
        ];
    }
}
