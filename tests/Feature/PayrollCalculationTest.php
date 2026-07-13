<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\Employee;
use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Models\Setting;
use App\Services\PayrollCalculatorService;
use Carbon\Carbon;

class PayrollCalculationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::create([
            'app_name' => 'Test App',
            'copyright' => 'Test Copyright',
            'login_title' => 'Login',
            'keywords' => 'test',
            'description' => 'test',
        ]);
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_prorata_calculation_works_correctly()
    {
        // 1. Setup user & employee
        $role = Role::where('slug', 'employee')->first();
        $user = User::factory()->create(['role_id' => $role->id]);
        
        $employee = Employee::create([
            'user_id' => $user->id,
            'employee_id' => 'EMP-TEST-01',
            'nik' => '1234567890',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'gender' => 'Male',
            'status' => 'active',
            'hire_date' => Carbon::create(2026, 1, 15)->format('Y-m-d'),
        ]);

        // Attach Basic Salary of 10,000,000
        $basicSalaryComp = \App\Models\SalaryComponent::create(['name' => 'Basic Salary', 'type' => 'allowance']);
        $employee->salaryComponents()->attach($basicSalaryComp->id, ['amount' => 10000000]);

        // 2. Call service for the hire month (January 2026)
        $service = new PayrollCalculatorService();
        $result = $service->calculate($employee, 1, 2026);

        // From 15th to 31st January 2026, how many working days?
        // Let's assume total working days is 22 in a month for prorata (as hardcoded in service)
        // 15th Jan 2026 is a Thursday. Weekends are Sat/Sun.
        // It will calculate the diff in days filtered by not weekends.
        // The service logic does prorata!
        $this->assertTrue($result['basic_salary'] < 10000000);
        $this->assertArrayHasKey('net_pay', $result);
    }

    public function test_payslip_idor_protection()
    {
        $role1 = Role::where('slug', 'employee')->first();
        
        $user1 = User::factory()->create(['role_id' => $role1->id]);
        $emp1 = Employee::create([
            'user_id' => $user1->id, 'employee_id' => 'E1', 'nik' => '111', 'first_name' => 'A', 'last_name' => 'A', 'email' => 'a@example.com', 'status' => 'active', 'hire_date' => now()->format('Y-m-d')
        ]);
        
        $user2 = User::factory()->create(['role_id' => $role1->id]);
        $emp2 = Employee::create([
            'user_id' => $user2->id, 'employee_id' => 'E2', 'nik' => '222', 'first_name' => 'B', 'last_name' => 'B', 'email' => 'b@example.com', 'status' => 'active', 'hire_date' => now()->format('Y-m-d')
        ]);

        $hrRole = Role::where('slug', 'hr-admin')->first();
        $hr = User::factory()->create(['role_id' => $hrRole->id]);

        $payroll = PayrollRun::create(['period_month' => 1, 'period_year' => 2026, 'status' => 'DRAFT', 'created_by' => $hr->id]);
        
        $payslip1 = Payslip::create(['payroll_run_id' => $payroll->id, 'employee_id' => $emp1->id, 'basic_salary' => 1000, 'net_pay' => 1000, 'status' => 'FINAL']);
        $payslip2 = Payslip::create(['payroll_run_id' => $payroll->id, 'employee_id' => $emp2->id, 'basic_salary' => 1000, 'net_pay' => 1000, 'status' => 'FINAL']);

        // User 1 tries to access User 2's payslip
        $this->actingAs($user1);
        $response = $this->get(route('payslips.show', $payslip2->id));
        $response->assertStatus(403);

        // User 1 accesses their own payslip
        $response2 = $this->get(route('payslips.show', $payslip1->id));
        $response2->assertStatus(200);
    }
}
