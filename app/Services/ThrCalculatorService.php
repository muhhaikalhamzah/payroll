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
        // Step 1: Pajak A (Reguler Disetahunkan)
        $annualizedRegularGross = $regularGrossPay * 12;
        $taxA = $this->calculateProgressiveTax($annualizedRegularGross);

        // Step 2: Pajak B (Reguler Disetahunkan + THR)
        $annualizedTotalGross = $annualizedRegularGross + $thrAmount;
        $taxB = $this->calculateProgressiveTax($annualizedTotalGross);

        // Step 3: Pajak B - Pajak A = PPh 21 THR
        $pph21Amount = max(0, $taxB - $taxA);

        $taxRecord = [
            'ter_category' => 'NON-REGULER (THR)',
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

    /**
     * Simplified Progressive Tax Calculation (Pasal 17).
     * Assuming simple PTKP deduction has already happened or simplified here.
     */
    private function calculateProgressiveTax(float $annualIncome): float
    {
        $ptkp = 54000000; // TK/0 assumption
        $pkp = max(0, $annualIncome - $ptkp);

        if ($pkp <= 0) return 0;

        $tax = 0;
        
        // Layer 1: 0 - 60,000,000 (5%)
        if ($pkp > 60000000) {
            $tax += 60000000 * 0.05;
            $pkp -= 60000000;
        } else {
            return $tax + ($pkp * 0.05);
        }

        // Layer 2: 60,000,000 - 250,000,000 (15%)
        if ($pkp > 190000000) {
            $tax += 190000000 * 0.15;
            $pkp -= 190000000;
        } else {
            return $tax + ($pkp * 0.15);
        }

        // Layer 3: 250,000,000 - 500,000,000 (25%)
        if ($pkp > 250000000) {
            $tax += 250000000 * 0.25;
            $pkp -= 250000000;
        } else {
            return $tax + ($pkp * 0.25);
        }

        // Layer 4: > 500,000,000 (30%)
        return $tax + ($pkp * 0.30);
    }
}
