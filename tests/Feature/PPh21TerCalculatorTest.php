<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Employee;
use App\Models\TaxTerRate;
use App\Models\Payslip;
use App\Models\PayrollRun;
use App\Services\PPh21CalculatorService;

class PPh21TerCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PPh21CalculatorService();
        $this->seed(\Database\Seeders\PPh21TerRatesSeeder::class);
    }

    public function test_ter_a_regular_and_december_reconciliation()
    {
        // Gaji 10jt, iuran pensiun 100k, PTKP K/0
        // PPh21 bulanan = 200.000
        // PPh21 setahun = 2.715.000, Des = 515.000
        
        $employee = Employee::factory()->create([
            'marital_status' => 'K',
            'dependents_count' => 0
        ]);

        $this->assertEquals('A', $this->service->getTerCategory($employee));

        // Test Monthly
        $monthlyTax = $this->service->calculateMonthlyTER($employee, 10000000);
        $this->assertEquals(200000, $monthlyTax);

        // Mock 11 months of payslips (Jan - Nov)
        for ($month = 1; $month <= 11; $month++) {
            $run = PayrollRun::create([
                'type' => 'REGULAR',
                'period_month' => $month,
                'period_year' => 2026,
                'status' => 'PAID',
                'created_by' => 1
            ]);
            
            $payslip = Payslip::create([
                'employee_id' => $employee->id,
                'payroll_run_id' => $run->id,
                'basic_salary' => 10000000,
                'total_allowances' => 0,
                'total_deductions' => 300000, // 200k tax + 100k pensiun
                'net_pay' => 9700000,
                'status' => 'FINAL'
            ]);

            \App\Models\PayslipComponent::create([
                'payslip_id' => $payslip->id,
                'name' => 'PPh 21',
                'amount' => 200000,
                'type' => 'deduction'
            ]);

            \App\Models\PayslipComponent::create([
                'payslip_id' => $payslip->id,
                'name' => 'Iuran Pensiun',
                'amount' => 100000,
                'type' => 'deduction'
            ]);
        }

        // Check counts
        if (PayrollRun::count() == 0 || Payslip::count() == 0) {
            dd([PayrollRun::count(), Payslip::count()]);
        }

        // Test December Reconciliation
        // December gross = 10000000, December JHT/JP = 100000
        $decemberTax = $this->service->calculateDecemberReconciliation($employee, 2026, 10000000, 100000);
        
        // PPh 21 Setahun = 2.715.000
        // Sudah dipotong = 11 * 200.000 = 2.200.000
        // Desember = 2.715.000 - 2.200.000 = 515.000
        $this->assertEquals(515000, $decemberTax);
    }

    public function test_ter_a_with_thr_combined()
    {
        // Status TK/0
        // Gross biasa 30.080.000 -> tarif 13% -> 3.910.400
        // Gross + THR = 50.080.000 -> tarif 18% -> 9.014.400
        $employee = Employee::factory()->create([
            'marital_status' => 'TK',
            'dependents_count' => 0
        ]);

        $this->assertEquals('A', $this->service->getTerCategory($employee));

        // Regular month
        $monthlyTax = $this->service->calculateMonthlyTER($employee, 30080000);
        $this->assertEquals(3910400, $monthlyTax);

        // THR month (combined)
        $thrMonthTax = $this->service->calculateMonthlyTER($employee, 50080000);
        $this->assertEquals(9014400, $thrMonthTax);
    }

    public function test_below_ptkp_zero_tax()
    {
        $employee = Employee::factory()->create([
            'marital_status' => 'TK',
            'dependents_count' => 0
        ]);

        // Gross 5jt (Di bawah batas bawah ter A yang kena tarif > 0, which is 5.4jt)
        // Wait, looking at ter_a table: 0 - 5.400.000 = 0%
        $monthlyTax = $this->service->calculateMonthlyTER($employee, 5000000);
        $this->assertEquals(0, $monthlyTax);
    }
}
