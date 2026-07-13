<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use App\Models\Notification;
use App\Models\Approval;
use Illuminate\Support\Facades\Auth;

function logStatus($message) {
    echo "- " . $message . "\n";
}

// ==========================================
// SCENARIO 1: LEAVE REQUEST
// ==========================================
echo "\n=== TESTING LEAVE REQUEST WORKFLOW ===\n";
// Find Employee and Approver
$employeeUser = User::where('email', 'employee1@payroll.com')->first();
$hrUser = User::where('email', 'hr1@payroll.com')->first();
$employee = Employee::where('user_id', $employeeUser->id)->first();

$balanceBefore = LeaveBalance::where('employee_id', $employee->id)->where('year', date('Y'))->first();
$balanceValBefore = $balanceBefore ? $balanceBefore->balance : 0;
logStatus("Initial Leave Balance: $balanceValBefore");

// 1. Submit Leave Request
Auth::login($employeeUser);
$requestData = [
    'leave_type_id' => 1,
    'start_date' => date('Y-m-d', strtotime('+1 day')),
    'end_date' => date('Y-m-d', strtotime('+2 days')), // 2 days
    'reason' => 'QA Testing Leave',
];

$app->instance(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class, new class {
    public function handle($request, $next) { return $next($request); }
});

// Trigger Submission via controller
$response = app()->handle(\Illuminate\Http\Request::create('/leave-requests', 'POST', $requestData));
logStatus("Submit Leave Request Route Status: " . $response->getStatusCode());

$createdRequest = LeaveRequest::where('reason', 'QA Testing Leave')->orderBy('id', 'desc')->first();
logStatus("Leave Request Status in DB: " . ($createdRequest->status ?? 'NOT FOUND'));

// Check notifications for HR
$notifications = \Illuminate\Support\Facades\DB::table('notifications')->where('notifiable_id', $hrUser->id)->get();
logStatus("Notifications for HR Admin: " . $notifications->count());

// 2. HR Approves
Auth::login($hrUser);
// Use ApprovalController
$approvalResponse = app()->handle(\Illuminate\Http\Request::create("/approvals/leave_request/{$createdRequest->id}/approve", 'POST', ['notes' => 'Approved in QA']));
logStatus("Approve Leave Request Route Status: " . $approvalResponse->getStatusCode());

$createdRequest->refresh();
logStatus("Leave Request Status after approval: " . $createdRequest->status);

$balanceAfter = LeaveBalance::where('employee_id', $employee->id)->where('year', date('Y'))->first();
$balanceValAfter = $balanceAfter ? $balanceAfter->balance : 0;
logStatus("Final Leave Balance: $balanceValAfter (Expected reduction by 2)");

$empNotifications = \Illuminate\Support\Facades\DB::table('notifications')->where('notifiable_id', $employeeUser->id)->get();
logStatus("Notifications for Employee: " . $empNotifications->count());

// ==========================================
// SCENARIO 2: EMPLOYEE LOAN
// ==========================================
echo "\n=== TESTING EMPLOYEE LOAN WORKFLOW ===\n";
Auth::login($employeeUser);
$loanData = [
    'employee_id' => $employee->id,
    'amount' => 5000000,
    'installments' => 5,
    'reason' => 'QA Testing Loan',
];
$loanResponse = app()->handle(\Illuminate\Http\Request::create('/employee-loans', 'POST', $loanData));
logStatus("Submit Loan Route Status: " . $loanResponse->getStatusCode());

$createdLoan = \App\Models\EmployeeLoan::where('reason', 'QA Testing Loan')->first();
logStatus("Loan Status in DB: " . ($createdLoan->status ?? 'NOT FOUND'));

Auth::login($hrUser);
$financeUser = User::where('email', 'finance1@payroll.com')->first();
$approvalLoanResp = app()->handle(\Illuminate\Http\Request::create("/approvals/employee_loan/{$createdLoan->id}/approve", 'POST', ['notes' => 'Loan Approved']));
logStatus("Approve Loan Route Status: " . $approvalLoanResp->getStatusCode());

Auth::login($financeUser);
$disburseResp = app()->handle(\Illuminate\Http\Request::create("/employee-loans/{$createdLoan->id}/disburse", 'POST'));
logStatus("Disburse Loan Route Status: " . $disburseResp->getStatusCode());

$createdLoan->refresh();
logStatus("Loan Status after disburse: " . $createdLoan->status);

// ==========================================
// SCENARIO 3: EMPTY STATES & ROLE SCOPES
// ==========================================
echo "\n=== TESTING EMPTY STATES & ROLE SCOPES ===\n";
Auth::login($employeeUser);
$getLeaveResp = app()->handle(\Illuminate\Http\Request::create('/leave-requests', 'GET'));
logStatus("Employee Access /leave-requests Status: " . $getLeaveResp->getStatusCode());

Auth::login($financeUser);
$getFinanceLeaveResp = app()->handle(\Illuminate\Http\Request::create('/leave-requests', 'GET'));
logStatus("Finance Access /leave-requests Status: " . $getFinanceLeaveResp->getStatusCode());

