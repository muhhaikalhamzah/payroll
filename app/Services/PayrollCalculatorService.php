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
        $overtimeRequests = OvertimeRequest::where('employee_id', $employee->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', 'APPROVED')
            ->get();

        $totalOvertimeMinutes = $overtimeRequests->sum('duration_minutes');
        $overtimeHours = $totalOvertimeMinutes / 60;
        $overtimePay = $overtimeHours * (1 / 173) * $basicSalary;
        
        if ($overtimePay > 0) {
            $allowances[] = [
                'name' => 'Overtime Pay',
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

        // 6. BPJS Deductions (Standard rates: JHT 2%, JP 1%, Kesehatan 1% from employee)
        $jhtAmount = $grossPay * 0.02;
        $jpAmount = $grossPay * 0.01;
        $kesAmount = $grossPay * 0.01;
        
        $bpjsRecord = [
            'jht_amount' => $jhtAmount,
            'jp_amount' => $jpAmount,
            'jkk_amount' => 0, // usually paid by employer
            'jkm_amount' => 0, // usually paid by employer
            'kesehatan_amount' => $kesAmount,
        ];

        $deductions[] = ['name' => 'BPJS JHT (2%)', 'amount' => $jhtAmount];
        $deductions[] = ['name' => 'BPJS JP (1%)', 'amount' => $jpAmount];
        $deductions[] = ['name' => 'BPJS Kesehatan (1%)', 'amount' => $kesAmount];

        // 7. PPh 21 (TER logic simplified)
        $terCategory = 'A';
        $pph21Rate = 0.05; 
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
