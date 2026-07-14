<?php

namespace App\Services;

use App\Models\Employee;
use Carbon\Carbon;

class ThrCalculatorService
{
    /**
     * Calculate THR for a given employee and period.
     *
     * @param Employee $employee
     * @param int $month
     * @param int $year
     * @return array
     */
    public function calculate(Employee $employee, int $month, int $year): array
    {
        // 1. Get Base Salary and Allowances
        $basicSalary = 0;
        $allowances = [];

        foreach ($employee->salaryComponents as $component) {
            if (strtolower($component->name) === 'basic salary' || strtolower($component->name) === 'gaji pokok') {
                $basicSalary = (float) $component->pivot->amount;
            } elseif ($component->type === 'allowance') {
                $allowances[] = [
                    'name' => $component->name,
                    'amount' => (float) $component->pivot->amount,
                ];
            }
        }

        $totalAllowances = array_sum(array_column($allowances, 'amount'));
        $regularGrossPay = $basicSalary + $totalAllowances;

        // 2. Calculate Prorated THR based on Hire Date
        $hireDate = Carbon::parse($employee->hire_date);
        $thrCutoffDate = Carbon::createFromDate($year, $month, 1)->endOfMonth(); // Assumption: THR calculation date
        $monthsOfService = $hireDate->diffInMonths($thrCutoffDate);

        $thrAmount = 0;
        if ($monthsOfService >= 12) {
            $thrAmount = $regularGrossPay; // 1 full month pay
        } elseif ($monthsOfService >= 1) {
            $thrAmount = ($monthsOfService / 12) * $regularGrossPay; // Prorated
        } else {
            $thrAmount = 0; // Not eligible
        }

        // 3. Tax Calculation (PPh 21 Non-Reguler)
        $taxStatus = $employee->tax_status ?? 'TK/0';

        // Step 1: Pajak A (Pajak Reguler bulanan menggunakan TER)
        $pph21Calc = new \App\Services\PPh21CalculatorService();
        $taxA = $pph21Calc->calculateMonthlyTER(
            $employee,
            $regularGrossPay
        );

        // Step 2: Pajak B (Pajak Reguler + THR menggunakan TER)
        $taxB = $pph21Calc->calculateMonthlyTER(
            $employee,
            $regularGrossPay + $thrAmount
        );

        // Step 3: Pajak B - Pajak A = PPh 21 THR
        $pph21Amount = max(0, $taxB - $taxA);

        $taxRecord = [
            'ter_category' => 'NON-REGULER (THR TER)',
            'bruto_amount' => $thrAmount,
            'pph21_amount' => $pph21Amount,
        ];

        // 4. Final Deductions (Only Tax for THR)
        $deductions = [];
        if ($pph21Amount > 0) {
            $deductions[] = [
                'name' => 'PPh 21 (THR)',
                'amount' => $pph21Amount,
            ];
        }

        $totalDeductions = array_sum(array_column($deductions, 'amount'));
        $netPay = $thrAmount - $totalDeductions;

        return [
            'basic_salary' => $thrAmount, // Storing THR as basic_salary for payslip display
            'total_allowances' => 0,
            'total_deductions' => $totalDeductions,
            'net_pay' => $netPay,
            'allowance_components' => [], // No extra allowances on THR slip
            'deduction_components' => $deductions,
            'tax_record' => $taxRecord,
            'bpjs_record' => [ // Zero out BPJS
                'jht_amount' => 0,
                'jp_amount' => 0,
                'jkk_amount' => 0,
                'jkm_amount' => 0,
                'kesehatan_amount' => 0,
            ],
            'needs_intervention' => false,
            'intervention_reason' => null,
            'meta' => [ // Meta to help show on UI
                'months_of_service' => $monthsOfService,
                'regular_gross' => $regularGrossPay,
                'tax_a' => $taxA,
                'tax_b' => $taxB
            ]
        ];
    }


}
