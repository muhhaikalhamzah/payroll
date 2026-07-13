<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Employee;
use App\Models\Department;
use App\Models\SalaryComponent;
use App\Models\NationalHoliday;
use Carbon\Carbon;
use App\Services\OvertimeCalculatorService;

class OvertimeCalculatorTest extends TestCase
{
    use RefreshDatabase;

    protected $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new OvertimeCalculatorService();
    }
    
    private function attachBasicSalary($employee, $amount) {
        $component = SalaryComponent::create([
            'name' => 'Basic Salary',
            'type' => 'allowance',
            'is_taxable' => true
        ]);
        $employee->salaryComponents()->attach($component->id, ['amount' => $amount]);
    }

    public function test_regular_workday_overtime()
    {
        $employee = Employee::factory()->create();
        $this->attachBasicSalary($employee, 1730000); // rate = 10000 per hour

        // Wednesday (Regular workday)
        $date = Carbon::parse('2026-07-15');

        // 3 hours: 1st hour 1.5x, next 2 hours 2x = 5.5 multiplier
        // 5.5 * 10000 = 55000
        $pay = $this->service->calculateOvertimeRate($employee, $date, 3);
        $this->assertEquals(55000, $pay);
    }

    public function test_5_days_rest_day_overtime()
    {
        $dept = Department::factory()->create(['work_schedule_type' => '5_days']);
        $employee = Employee::factory()->create(['department_id' => $dept->id]);
        $this->attachBasicSalary($employee, 1730000);

        // Saturday (Rest day for 5_days)
        $date = Carbon::parse('2026-07-18');

        // 9 hours: 8 hours * 2 = 16, 1 hour * 3 = 3. Total multiplier = 19
        // 19 * 10000 = 190000
        $pay = $this->service->calculateOvertimeRate($employee, $date, 9);
        $this->assertEquals(190000, $pay);
    }

    public function test_6_days_rest_day_overtime()
    {
        $dept = Department::factory()->create(['work_schedule_type' => '6_days']);
        $employee = Employee::factory()->create(['department_id' => $dept->id]);
        $this->attachBasicSalary($employee, 1730000);

        // Sunday (Rest day for 6_days)
        $date = Carbon::parse('2026-07-19');

        // 9 hours: 7 hours * 2 = 14, 1 hour * 3 = 3, 1 hour * 4 = 4. Total multiplier = 21
        // 21 * 10000 = 210000
        $pay = $this->service->calculateOvertimeRate($employee, $date, 9);
        $this->assertEquals(210000, $pay);
    }

    public function test_national_holiday_overtime()
    {
        $dept = Department::factory()->create(['work_schedule_type' => '5_days']);
        $employee = Employee::factory()->create(['department_id' => $dept->id]);
        $this->attachBasicSalary($employee, 1730000);

        // Wednesday but it's a National Holiday
        $date = Carbon::parse('2026-07-15');
        NationalHoliday::create(['date' => '2026-07-15', 'description' => 'Holiday']);

        // 9 hours: same as 5_days rest day = 19 multiplier
        $pay = $this->service->calculateOvertimeRate($employee, $date, 9);
        $this->assertEquals(190000, $pay);
    }
}
