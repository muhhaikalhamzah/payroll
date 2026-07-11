<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Employee;
use App\Models\AttendanceRecord;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\Permission;

class AttendanceOvertimeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->seed(\Database\Seeders\RolePermissionSeeder::class);
    }

    public function test_cannot_request_overtime_without_attendance()
    {
        $role = Role::where('slug', 'employee')->first();
        $user = User::factory()->create(['role_id' => $role->id]);
        $employee = Employee::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->post(route('overtime-requests.store'), [
            'employee_id' => $employee->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'duration_minutes' => 120,
            'reason' => 'Working late',
            'status' => 'PENDING_MANAGER'
        ]);

        // Should be redirected back with error
        $response->assertSessionHas('error');
        $this->assertEquals(0, OvertimeRequest::count());
    }

    public function test_can_request_overtime_with_attendance()
    {
        $role = Role::where('slug', 'employee')->first();
        $user = User::factory()->create(['role_id' => $role->id]);
        $employee = Employee::factory()->create(['user_id' => $user->id]);

        AttendanceRecord::create([
            'employee_id' => $employee->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'status' => 'hadir'
        ]);

        $this->actingAs($user);

        $response = $this->post(route('overtime-requests.store'), [
            'employee_id' => $employee->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'duration_minutes' => 120,
            'reason' => 'Working late',
            'status' => 'PENDING_MANAGER'
        ]);

        // Should be successful
        $response->assertSessionHas('success');
        $this->assertEquals(1, OvertimeRequest::count());
    }

    public function test_unauthorized_user_cannot_approve_overtime()
    {
        $role = Role::where('slug', 'employee')->first();
        $employeeUser = User::factory()->create(['role_id' => $role->id]);
        $employee = Employee::factory()->create(['user_id' => $employeeUser->id]);

        AttendanceRecord::create([
            'employee_id' => $employee->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'status' => 'hadir'
        ]);

        $overtime = OvertimeRequest::create([
            'employee_id' => $employee->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'duration_minutes' => 120,
            'reason' => 'Working late',
            'status' => 'PENDING_MANAGER'
        ]);

        $this->actingAs($employeeUser);

        $response = $this->put(route('overtime-requests.update', $overtime->id), [
            'employee_id' => $employee->id,
            'date' => Carbon::now()->format('Y-m-d'),
            'duration_minutes' => 120,
            'reason' => 'Working late',
            'status' => 'APPROVED'
        ]);

        $response->assertStatus(403); // Unauthorized because Policy blocks it
        $this->assertEquals('PENDING_MANAGER', $overtime->fresh()->status);
    }
}
