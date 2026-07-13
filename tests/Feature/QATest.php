<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\EmployeeLoan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class QATest extends TestCase
{
    public function test_leave_request_workflow()
    {
        $employeeUser = User::where('email', 'employee1@payroll.com')->first();
        $hrUser = User::where('email', 'hr1@payroll.com')->first();
        $employee = Employee::where('user_id', $employeeUser->id)->first();

        // Check Initial Balance
        $initialBalance = LeaveBalance::where('employee_id', $employee->id)->where('year', date('Y'))->first()->balance ?? 0;
        dump("Initial Balance: " . $initialBalance);

        // 1. Submit
        $response = $this->actingAs($employeeUser)->post('/leave-requests', [
            'leave_type_id' => 1,
            'start_date' => date('Y-m-d', strtotime('+1 day')),
            'end_date' => date('Y-m-d', strtotime('+2 days')),
            'reason' => 'QA Testing Leave',
        ]);
        $response->assertStatus(302);

        $leaveReq = LeaveRequest::where('reason', 'QA Testing Leave')->first();
        dump("Leave Request Status: " . ($leaveReq->status ?? 'NOT FOUND'));

        // HR Notifications
        $notifs = DB::table('notifications')->where('notifiable_id', $hrUser->id)->count();
        dump("Notifications for HR: " . $notifs);

        // 2. Approve
        $approvalRes = $this->actingAs($hrUser)->post("/approvals/leave_request/{$leaveReq->id}/approve", [
            'notes' => 'OK'
        ]);
        $approvalRes->assertStatus(302);

        $leaveReq->refresh();
        dump("Leave Request Status after approve: " . $leaveReq->status);

        $finalBalance = LeaveBalance::where('employee_id', $employee->id)->where('year', date('Y'))->first()->balance ?? 0;
        dump("Final Balance: " . $finalBalance);

        $empNotifs = DB::table('notifications')->where('notifiable_id', $employeeUser->id)->count();
        dump("Notifications for Employee: " . $empNotifs);
    }

    public function test_employee_loan_workflow()
    {
        $employeeUser = User::where('email', 'employee1@payroll.com')->first();
        $hrUser = User::where('email', 'hr1@payroll.com')->first();
        $financeUser = User::where('email', 'finance1@payroll.com')->first();
        $employee = Employee::where('user_id', $employeeUser->id)->first();

        $response = $this->actingAs($employeeUser)->post('/employee-loans', [
            'employee_id' => $employee->id,
            'amount' => 1000000,
            'installments' => 5,
            'reason' => 'QA Testing Loan',
        ]);
        $response->assertStatus(302);

        $loan = EmployeeLoan::where('reason', 'QA Testing Loan')->first();
        dump("Loan Status: " . ($loan->status ?? 'NOT FOUND'));

        $this->actingAs($hrUser)->post("/approvals/employee_loan/{$loan->id}/approve", ['notes' => 'HR OK']);
        
        $disburseRes = $this->actingAs($financeUser)->post("/employee-loans/{$loan->id}/disburse");
        $loan->refresh();
        dump("Loan Status after disburse: " . $loan->status);
    }

    public function test_role_boundaries()
    {
        $employeeUser = User::where('email', 'employee1@payroll.com')->first();
        $financeUser = User::where('email', 'finance1@payroll.com')->first();

        // Employee accesses something they shouldn't
        $res = $this->actingAs($employeeUser)->get('/leave-balances');
        dump("Employee accessing /leave-balances: " . $res->status()); // Should be 403

        // Finance accesses leave requests
        $res = $this->actingAs($financeUser)->get('/leave-requests');
        dump("Finance accessing /leave-requests: " . $res->status());
    }
}
