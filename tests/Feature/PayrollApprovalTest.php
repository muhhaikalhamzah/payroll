<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Role;
use App\Models\PayrollRun;

class PayrollApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        // Reload gates since AppServiceProvider boot runs before this setup
        $permissions = \App\Models\Permission::with('roles')->get();
        foreach ($permissions as $permission) {
            \Illuminate\Support\Facades\Gate::define($permission->slug, function (\App\Models\User $user) use ($permission) {
                return $user->role_id ? $permission->roles->contains('id', $user->role_id) : false;
            });
        }
    }

    public function test_hr_can_submit_payroll()
    {
        $hrAdmin = User::factory()->create([
            'role_id' => Role::where('slug', 'hr-admin')->first()->id
        ]);

        $payrollRun = PayrollRun::create([
            'period_month' => 1,
            'period_year' => 2024,
            'status' => 'DRAFT',
            'created_by' => $hrAdmin->id,
        ]);

        $response = $this->actingAs($hrAdmin)->post(route('approvals.submit', ['type' => 'payroll_run', 'id' => $payrollRun->id]), [
            'notes' => 'Please approve'
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals('PENDING_FINANCE', $payrollRun->fresh()->status);
        $this->assertDatabaseHas('approvals', [
            'approvable_type' => 'App\Models\PayrollRun',
            'approvable_id' => $payrollRun->id,
            'status' => 'PENDING_FINANCE',
            'notes' => 'Please approve'
        ]);
        
        $this->assertDatabaseHas('approval_logs', [
            'actor_id' => $hrAdmin->id,
            'action' => 'SUBMITTED'
        ]);
    }

    public function test_finance_can_approve_payroll()
    {
        $financeAdmin = User::factory()->create([
            'role_id' => Role::where('slug', 'finance-admin')->first()->id
        ]);

        $hrAdmin = User::factory()->create();

        $payrollRun = PayrollRun::create([
            'period_month' => 1,
            'period_year' => 2024,
            'status' => 'PENDING_FINANCE',
            'created_by' => $hrAdmin->id,
        ]);
        
        // Manually create the pending approval
        $approval = $payrollRun->submitForApproval('Pending');

        $response = $this->actingAs($financeAdmin)->post(route('approvals.approve', ['type' => 'payroll_run', 'id' => $payrollRun->id]), [
            'comments' => 'Looks good'
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals('APPROVED', $payrollRun->fresh()->status);
        
        $this->assertDatabaseHas('approvals', [
            'id' => $approval->id,
            'status' => 'APPROVED',
            'approver_id' => $financeAdmin->id
        ]);
        
        $this->assertDatabaseHas('approval_logs', [
            'approval_id' => $approval->id,
            'actor_id' => $financeAdmin->id,
            'action' => 'APPROVED'
        ]);
    }

    public function test_finance_can_reject_payroll()
    {
        $financeAdmin = User::factory()->create([
            'role_id' => Role::where('slug', 'finance-admin')->first()->id
        ]);

        $hrAdmin = User::factory()->create();

        $payrollRun = PayrollRun::create([
            'period_month' => 1,
            'period_year' => 2024,
            'status' => 'PENDING_FINANCE',
            'created_by' => $hrAdmin->id,
        ]);
        
        $approval = $payrollRun->submitForApproval('Pending');

        $response = $this->actingAs($financeAdmin)->post(route('approvals.reject', ['type' => 'payroll_run', 'id' => $payrollRun->id]), [
            'comments' => 'Missing data'
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals('DRAFT', $payrollRun->fresh()->status);
        
        $this->assertDatabaseHas('approvals', [
            'id' => $approval->id,
            'status' => 'REJECTED'
        ]);
    }
}
