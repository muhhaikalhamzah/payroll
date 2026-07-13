<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\PayrollRun;
use App\Models\Employee;
use App\Models\Payslip;
use App\Models\PayslipComponent;
use App\Models\TaxRecord;
use App\Models\BpjsRecord;
use App\Services\ThrCalculatorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateThrJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $thrRunId;

    public function __construct($thrRunId)
    {
        $this->thrRunId = $thrRunId;
    }

    public function handle(ThrCalculatorService $calculator): void
    {
        $thrRun = PayrollRun::find($this->thrRunId);
        if (!$thrRun || $thrRun->status !== 'DRAFT' || $thrRun->type !== 'THR') {
            Log::warning("GenerateThrJob aborted for run {$this->thrRunId}: Invalid state or type.");
            return;
        }

        try {
            Employee::where('status', 'active')->chunkById(100, function ($employees) use ($calculator, $thrRun) {
                foreach ($employees as $employee) {
                    DB::transaction(function () use ($employee, $calculator, $thrRun) {
                        // Delete existing draft payslip for this run and employee if any
                        Payslip::where('payroll_run_id', $thrRun->id)
                            ->where('employee_id', $employee->id)
                            ->delete();

                        $calcResult = $calculator->calculate($employee, $thrRun->period_month, $thrRun->period_year);

                        // Save meta information in intervention_reason to display it easily without schema changes
                        $metaString = "Months of Service: " . $calcResult['meta']['months_of_service'] . 
                            "\nRegular Gross: " . number_format($calcResult['meta']['regular_gross'], 2) . 
                            "\nTax A (Annual Regular): " . number_format($calcResult['meta']['tax_a'], 2) . 
                            "\nTax B (Annual Total): " . number_format($calcResult['meta']['tax_b'], 2);

                        $payslip = Payslip::create([
                            'payroll_run_id' => $thrRun->id,
                            'employee_id' => $employee->id,
                            'basic_salary' => $calcResult['basic_salary'],
                            'total_allowances' => $calcResult['total_allowances'],
                            'total_deductions' => $calcResult['total_deductions'],
                            'net_pay' => $calcResult['net_pay'],
                            'status' => 'DRAFT',
                            'needs_intervention' => $calcResult['needs_intervention'],
                            'intervention_reason' => $metaString,
                        ]);

                        foreach ($calcResult['allowance_components'] as $allowance) {
                            PayslipComponent::create([
                                'payslip_id' => $payslip->id,
                                'name' => $allowance['name'],
                                'amount' => $allowance['amount'],
                                'type' => 'allowance',
                            ]);
                        }

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
                    });
                }
            });
            
            Log::info("THR generated successfully for run {$this->thrRunId}");
        } catch (\Exception $e) {
            Log::error("GenerateThrJob failed: " . $e->getMessage());
        }
    }
}
