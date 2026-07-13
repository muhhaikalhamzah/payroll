<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Position;
use App\Models\Employee;
use App\Models\SalaryComponent;
use App\Models\User;
use Carbon\Carbon;
use App\Jobs\GeneratePayrollJob;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyPayrollSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Department and Position
        $dept = Department::firstOrCreate(['name' => 'Finance & Accounting'], ['description' => 'Finance Dept']);
        
        $posManager = Position::firstOrCreate(
            ['name' => 'Finance Manager'],
            ['department_id' => $dept->id]
        );
        $posStaff = Position::firstOrCreate(
            ['name' => 'Finance Staff'],
            ['department_id' => $dept->id]
        );

        // 2. Create Salary Components
        $compBase = SalaryComponent::firstOrCreate(['name' => 'Basic Salary'], ['type' => 'allowance']);
        $compTransport = SalaryComponent::firstOrCreate(['name' => 'Transport Allowance'], ['type' => 'allowance']);
        $compMeal = SalaryComponent::firstOrCreate(['name' => 'Meal Allowance'], ['type' => 'allowance']);

        // 3. Create Employees with different Tax Status (PTKP)
        // Manager - K/2 (High income, married 2 dependents)
        $managerUser = User::firstOrCreate(
            ['email' => 'manager_finance@example.com'],
            ['name' => 'Budi Santoso', 'password' => Hash::make('password123'), 'role_id' => 2] // Assumes role 2 is employee
        );

        $manager = Employee::firstOrCreate(
            ['nik' => '1234567890123456'],
            [
                'user_id' => $managerUser->id,
                'first_name' => 'Budi',
                'last_name' => 'Santoso',
                'email' => 'manager_finance@example.com',
                'phone' => '081234567890',
                'hire_date' => Carbon::now()->subYears(3),
                'department_id' => $dept->id,
                'position_id' => $posManager->id,
                'status' => 'active',
                'tax_status' => 'K/2'
            ]
        );

        // Assign Salary Components for Manager
        $manager->salaryComponents()->sync([
            $compBase->id => ['amount' => 15000000],
            $compTransport->id => ['amount' => 1500000],
            $compMeal->id => ['amount' => 1000000],
        ]);

        // Staff - TK/0 (Medium income, Single 0 dependents)
        $staffUser = User::firstOrCreate(
            ['email' => 'staff_finance@example.com'],
            ['name' => 'Siti Aminah', 'password' => Hash::make('password123'), 'role_id' => 2]
        );

        $staff = Employee::firstOrCreate(
            ['nik' => '1234567890123457'],
            [
                'user_id' => $staffUser->id,
                'first_name' => 'Siti',
                'last_name' => 'Aminah',
                'email' => 'staff_finance@example.com',
                'phone' => '081234567891',
                'hire_date' => Carbon::now()->subYears(1),
                'department_id' => $dept->id,
                'position_id' => $posStaff->id,
                'status' => 'active',
                'tax_status' => 'TK/0'
            ]
        );

        // Assign Salary Components for Staff
        $staff->salaryComponents()->sync([
            $compBase->id => ['amount' => 8000000],
            $compTransport->id => ['amount' => 500000],
            $compMeal->id => ['amount' => 500000],
        ]);

        $this->command->info('Dummy Employees created successfully!');

        // 4. Generate Payroll Run
        $payrollRun = \App\Models\PayrollRun::create([
            'period_month' => date('n'),
            'period_year' => date('Y'),
            'type' => 'REGULAR', // assuming added by type migration
            'status' => 'PAID',
            'created_by' => $managerUser->id,
        ]);

        $this->command->info('Calculating Payroll...');
        $payrollService = new \App\Services\PayrollCalculatorService();
        $employees = Employee::where('status', 'active')->get();

        $totalGross = 0;
        $totalDeductions = 0;
        $totalNet = 0;

        foreach ($employees as $emp) {
            $calc = $payrollService->calculate($emp, $payrollRun->period_month, $payrollRun->period_year);
            
            // Create Payslip
            $payslip = \App\Models\Payslip::create([
                'payroll_run_id' => $payrollRun->id,
                'employee_id' => $emp->id,
                'status' => 'FINAL',
                'basic_salary' => $calc['basic_salary'],
                'total_allowances' => $calc['total_allowances'],
                'total_deductions' => $calc['total_deductions'],
                'net_pay' => $calc['net_pay']
            ]);

            // Save components
            foreach ($calc['allowance_components'] ?? [] as $comp) {
                \App\Models\PayslipComponent::create([
                    'payslip_id' => $payslip->id,
                    'name' => $comp['name'],
                    'type' => 'allowance',
                    'amount' => $comp['amount']
                ]);
            }
            foreach ($calc['deduction_components'] ?? [] as $comp) {
                \App\Models\PayslipComponent::create([
                    'payslip_id' => $payslip->id,
                    'name' => $comp['name'],
                    'type' => 'deduction',
                    'amount' => $comp['amount']
                ]);
            }

            // Save Tax Record
            if (isset($calc['tax_record'])) {
                \App\Models\TaxRecord::create([
                    'payslip_id' => $payslip->id,
                    'bruto_amount' => $calc['tax_record']['bruto_amount'] ?? 0,
                    'pph21_amount' => $calc['tax_record']['pph21_amount'] ?? 0,
                    'ter_category' => $calc['tax_record']['ter_category'] ?? null,
                ]);
            }

            // Save BPJS Record
            if (isset($calc['bpjs_record'])) {
                \App\Models\BpjsRecord::create([
                    'payslip_id' => $payslip->id,
                    'jht_amount' => $calc['bpjs_record']['jht_amount'] ?? 0,
                    'jp_amount' => $calc['bpjs_record']['jp_amount'] ?? 0,
                    'kesehatan_amount' => $calc['bpjs_record']['kesehatan_amount'] ?? 0,
                    'jkk_amount' => $calc['bpjs_record']['jkk_amount'] ?? 0,
                    'jkm_amount' => $calc['bpjs_record']['jkm_amount'] ?? 0,
                ]);
            }
        }

        $this->command->info('Payroll Generated successfully. Payslips ready!');
    }
}
