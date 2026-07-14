<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$employee = \App\Models\Employee::first();

if (!$employee) {
    echo "No employee found in database.\n";
    exit;
}

$salaryComponent = \App\Models\SalaryComponent::firstOrCreate(
    ['name' => 'Basic Salary'],
    ['type' => 'allowance']
);

$employee->salaryComponents()->syncWithoutDetaching([
    $salaryComponent->id => ['amount' => 15000000] // Rp 15.000.000
]);

echo "Employee: " . $employee->user->name . " | PTKP: " . $employee->ptkp_status . "\n";

$run = \App\Models\PayrollRun::create([
    'period_month' => 7,
    'period_year' => 2026,
    'type' => 'REGULAR',
    'status' => 'DRAFT',
    'created_by' => \App\Models\User::first()->id ?? 1
]);

$service = new \App\Services\PayrollCalculatorService();
$result = $service->calculate($employee, 7, 2026);

$payslip = \App\Models\Payslip::create([
    'payroll_run_id' => $run->id,
    'employee_id' => $employee->id,
    'basic_salary' => $result['basic_salary'],
    'total_allowances' => $result['total_allowances'],
    'total_deductions' => $result['total_deductions'],
    'net_pay' => $result['net_pay'],
    'status' => 'FINAL',
]);

foreach ($result['allowance_components'] as $comp) {
    \App\Models\PayslipComponent::create([
        'payslip_id' => $payslip->id,
        'name' => $comp['name'],
        'amount' => $comp['amount'],
        'type' => 'allowance'
    ]);
}

foreach ($result['deduction_components'] as $comp) {
    \App\Models\PayslipComponent::create([
        'payslip_id' => $payslip->id,
        'name' => $comp['name'],
        'amount' => $comp['amount'],
        'type' => 'deduction'
    ]);
}

echo "Payslip ID: " . $payslip->id . "\n";
echo "Gross Pay: Rp " . number_format(($result['basic_salary'] + $result['total_allowances']), 0, ',', '.') . "\n";
echo "Net Pay: Rp " . number_format($result['net_pay'], 0, ',', '.') . "\n";

$taxComponent = \App\Models\PayslipComponent::where('payslip_id', $payslip->id)->where('name', 'PPh 21')->first();
if ($taxComponent) {
    echo "PPh 21 Deduction: Rp " . number_format($taxComponent->amount, 0, ',', '.') . "\n";
} else {
    echo "No PPh 21 Deducted (amount 0 or below PTKP).\n";
}
