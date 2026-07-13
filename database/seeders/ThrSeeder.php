<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PayrollRun;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\PayslipComponent;
use App\Models\TaxRecord;
use App\Models\BpjsRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ThrSeeder extends Seeder
{
    public function run(): void
    {
        // Add THR for March 2026 (Eid is around late March/April 2026)
        $month = 3;
        $year = 2026;

        $hrAdmin = User::whereHas('role', function($q) { $q->where('slug', 'hr-admin'); })->first();
        if (!$hrAdmin) return;

        DB::beginTransaction();
        try {
            $thrRun = PayrollRun::create([
                'type' => 'THR',
                'period_month' => $month,
                'period_year' => $year,
                'status' => 'APPROVED',
                'created_by' => $hrAdmin->id,
            ]);

            $calculator = new \App\Services\ThrCalculatorService();
            $employees = Employee::where('status', 'active')->get();

            foreach ($employees as $employee) {
                $calcResult = $calculator->calculate($employee, $month, $year);

                if ($calcResult['basic_salary'] <= 0) continue; // No THR

                // Provide a dummy meta for the UI
                $metaString = "Months of Service: " . $calcResult['meta']['months_of_service'] . 
                            "\nRegular Gross: " . number_format($calcResult['meta']['regular_gross'], 2) . 
                            "\nTax A (Annual Regular): " . number_format($calcResult['meta']['tax_a'], 2) . 
                            "\nTax B (Annual Total): " . number_format($calcResult['meta']['tax_b'], 2);

                $payslip = Payslip::create([
                    'payroll_run_id' => $thrRun->id,
                    'employee_id' => $employee->id,
                    'basic_salary' => $calcResult['basic_salary'], // Actually THR
                    'total_allowances' => 0,
                    'total_deductions' => $calcResult['total_deductions'],
                    'net_pay' => $calcResult['net_pay'],
                    'status' => 'FINAL',
                    'needs_intervention' => $calcResult['needs_intervention'],
                    'intervention_reason' => $metaString,
                ]);

                foreach ($calcResult['deduction_components'] as $deduction) {
                    PayslipComponent::create([
                        'payslip_id' => $payslip->id,
                        'name' => $deduction['name'],
                        'amount' => $deduction['amount'],
                        'type' => 'deduction',
                    ]);
                }

                TaxRecord::create(array_merge(['payslip_id' => $payslip->id], $calcResult['tax_record']));
                BpjsRecord::create(array_merge(['payslip_id' => $payslip->id], $calcResult['bpjs_record']));
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            echo "Error seeding THR: " . $e->getMessage() . "\n";
        }
    }
}
