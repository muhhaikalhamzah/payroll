<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\TaxTerRate;
use App\Models\Payslip;
use Carbon\Carbon;

class PPh21CalculatorService
{
    /**
     * Get TER Category (A/B/C) based on PTKP string.
     */
    public function getTerCategory(Employee $employee): string
    {
        $ptkp = $employee->ptkp_status;
        return config("tax.ter_categories.{$ptkp}", 'A');
    }

    /**
     * Calculate monthly tax using TER table.
     * Sumber: PMK 168/2023, lampiran resmi DJP (pajak.go.id)
     */
    public function calculateMonthlyTER(Employee $employee, float $grossIncome): float
    {
        $category = $this->getTerCategory($employee);

        // Find applicable rate tier
        $rateTier = TaxTerRate::where('kategori', $category)
            ->where('batas_bawah', '<=', $grossIncome)
            ->where(function ($query) use ($grossIncome) {
                $query->where('batas_atas', '>=', $grossIncome)
                      ->orWhereNull('batas_atas');
            })
            ->first();

        if (!$rateTier) {
            return 0; // Fallback, shouldn't happen with correct DB
        }

        return $grossIncome * (float) $rateTier->tarif;
    }

    /**
     * Calculate December reconciliation (Progressive Pasal 17).
     * Sumber: PMK 168/2023, lampiran resmi DJP (pajak.go.id)
     */
    public function calculateDecemberReconciliation(Employee $employee, int $year, float $decemberGross, float $decemberJhtJp): float
    {
        // 1. Get total gross income and total paid tax for Jan-Nov
        $janNovPayslips = Payslip::where('employee_id', $employee->id)
            ->whereHas('payrollRun', function($q) use ($year) {
                $q->where('period_year', $year)
                  ->where('period_month', '<', 12)
                  ->where('status', 'PAID');
            })
            ->get();

        $previousGross = $janNovPayslips->sum('basic_salary') + $janNovPayslips->sum('total_allowances');
        
        $previousTax = 0;
        $previousJhtJp = 0;

        foreach ($janNovPayslips as $payslip) {
            $deductions = $payslip->components()->where('type', 'deduction')->get();
            foreach ($deductions as $deduction) {
                $name = strtolower($deduction->name);
                if (str_contains($name, 'pph 21')) {
                    $previousTax += $deduction->amount;
                }
                if (str_contains($name, 'bpjs jht') || 
                    str_contains($name, 'bpjs jp') ||
                    str_contains($name, 'iuran pensiun')) {
                    $previousJhtJp += $deduction->amount;
                }
            }
        }

        // 2. Annual Gross and Deductions
        $annualGross = $previousGross + $decemberGross;
        $annualJhtJp = $previousJhtJp + $decemberJhtJp;
        $biayaJabatan = min(6000000, $annualGross * 0.05); // Max 6jt/tahun atau 500rb/bulan
        
        $netIncome = $annualGross - $biayaJabatan - $annualJhtJp;

        // 3. Subtract PTKP
        $ptkpStatus = $employee->ptkp_status;
        $ptkpAmount = config("tax.ptkp_tahunan.{$ptkpStatus}", 54000000);

        $pkp = max(0, $netIncome - $ptkpAmount);

        // Bulatkan PKP ke bawah ke ribuan penuh
        $pkp = floor($pkp / 1000) * 1000;

        // 4. Calculate Annual Tax using Pasal 17
        $annualTax = $this->calculatePasal17($pkp);

        // 5. Calculate December Tax (Annual Tax - Tax Paid Jan-Nov)
        $decemberTax = $annualTax - $previousTax;
        
        if (app()->environment('testing') && $decemberTax == 0) {
            dd([
                'prev_gross' => $previousGross,
                'dec_gross' => $decemberGross,
                'annual_gross' => $annualGross,
                'prev_tax' => $previousTax,
                'annual_tax' => $annualTax,
                'pkp' => $pkp,
                'net' => $netIncome,
                'biayaJabatan' => $biayaJabatan
            ]);
        }

        // If negative, it means overpaid (lebih bayar). In typical payroll, it might be returned or carried forward.
        return $decemberTax;
    }

    /**
     * Calculate progressive tax using Pasal 17 rates
     */
    private function calculatePasal17(float $pkp): float
    {
        if ($pkp <= 0) return 0;

        $pasal17Rates = TaxTerRate::where('kategori', 'PASAL17')->orderBy('no_lapisan')->get();
        $tax = 0;
        $remainingPkp = $pkp;

        foreach ($pasal17Rates as $tier) {
            $batasBawah = (float) $tier->batas_bawah;
            $batasAtas = $tier->batas_atas ? (float) $tier->batas_atas : INF;
            $tarif = (float) $tier->tarif;

            $tierSize = $batasAtas - $batasBawah;

            if ($remainingPkp > 0) {
                if ($remainingPkp > $tierSize) {
                    $tax += $tierSize * $tarif;
                    $remainingPkp -= $tierSize;
                } else {
                    $tax += $remainingPkp * $tarif;
                    $remainingPkp = 0;
                }
            }
        }

        return $tax;
    }
}
