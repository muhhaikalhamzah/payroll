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
use App\Services\PayrollCalculatorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeneratePayrollJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $payrollRunId;

    public function __construct($payrollRunId)
    {
        $this->payrollRunId = $payrollRunId;
    }

    public function handle(PayrollCalculatorService $calculator): void
    {
        $payrollRun = PayrollRun::find($this->payrollRunId);
        if (!$payrollRun || $payrollRun->status !== 'DRAFT') {
            Log::warning("GeneratePayrollJob aborted for run {$this->payrollRunId}: Invalid state.");
            return;
        }

        try {
            Employee::where('status', 'active')->chunkById(100, function ($employees) use ($calculator, $payrollRun) {
                foreach ($employees as $employee) {
                    DB::transaction(function () use ($employee, $calculator, $payrollRun) {
                        // Delete existing draft payslip for this run and employee if any (idempotency)
                        Payslip::where('payroll_run_id', $payrollRun->id)
                            ->where('employee_id', $employee->id)
                            ->delete();

                        $calcResult = $calculator->calculate($employee, $payrollRun->period_month, $payrollRun->period_year);

                        $payslip = Payslip::create([
                            'payroll_run_id' => $payrollRun->id,
                            'employee_id' => $employee->id,
                            'basic_salary' => $calcResult['basic_salary'],
                            'total_allowances' => $calcResult['total_allowances'],
                            'total_deductions' => $calcResult['total_deductions'],
                            'net_pay' => $calcResult['net_pay'],
                            'status' => 'DRAFT'
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
            
            // Note: Keep status as DRAFT so HR can review it before advancing to PENDING_FINANCE
            Log::info("Payroll generated successfully for run {$this->payrollRunId}");
        } catch (\Exception $e) {
            Log::error("GeneratePayrollJob failed: " . $e->getMessage());
        }
    }
}
