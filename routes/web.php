<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\PositionController;
use App\Http\Controllers\SalaryComponentController;
use App\Http\Controllers\SalaryStructureController;
Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/', [LoginController::class, 'index'])->name('login');
    Route::post('/authenticate', [LoginController::class, 'authenticate'])->name('login.authenticate');
});

Route::middleware('auth')->group(function () {
    Route::get('/logout', [LoginController::class, 'logout'])->name('login.logout');
    Route::post('/switch-user', [LoginController::class, 'switchUser'])->name('login.switch_user');
    
    Route::get('/global-search', [\App\Http\Controllers\GlobalSearchController::class, 'search'])->name('global.search');
    
    Route::get('/language/{locale}', [\App\Http\Controllers\LanguageController::class, 'switchLang'])->name('language.switch');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/show', [DashboardController::class, 'show'])->name('dashboard.show');
    Route::get('/dashboard/edit', [DashboardController::class, 'edit'])->name('dashboard.edit');
    Route::put('/dashboard/update', [DashboardController::class, 'update'])->name('dashboard.update');

    Route::resource('/user', UserController::class);

    // Master Data
    Route::resource('/departments', DepartmentController::class)->except(['show']);
    Route::resource('/positions', PositionController::class)->except(['show']);
    Route::resource('/salary-components', SalaryComponentController::class)->except(['show']);
    Route::resource('/salary-structures', SalaryStructureController::class)->except(['show']);
    Route::resource('thr-runs', \App\Http\Controllers\ThrRunController::class)->only(['index', 'create', 'store', 'show']);
    
    // Approvals for THR Runs
    Route::post('/thr-runs/{id}/submit', [\App\Http\Controllers\ApprovalController::class, 'submit'])->defaults('type', 'payroll_run')->name('thr-runs.submit');
    Route::post('/thr-runs/{id}/approve', [\App\Http\Controllers\ApprovalController::class, 'approve'])->defaults('type', 'payroll_run')->name('thr-runs.approve');
    Route::post('/thr-runs/{id}/reject', [\App\Http\Controllers\ApprovalController::class, 'reject'])->defaults('type', 'payroll_run')->name('thr-runs.reject');
    Route::post('/thr-runs/{id}/mark-paid', [\App\Http\Controllers\ApprovalController::class, 'markPaid'])->defaults('type', 'payroll_run')->name('thr-runs.mark-paid');

    Route::resource('/leave-types', \App\Http\Controllers\LeaveTypeController::class)->except(['show']);
    
    // Employee Data Management
    Route::resource('/employees', \App\Http\Controllers\EmployeeController::class);
    Route::post('/employees/{employee}/bank-accounts', [\App\Http\Controllers\EmployeeBankAccountController::class, 'store'])->name('employees.bank-accounts.store');
    Route::put('/employees/{employee}/bank-accounts/{bank_account}', [\App\Http\Controllers\EmployeeBankAccountController::class, 'update'])->name('employees.bank-accounts.update');
    Route::delete('/employees/{employee}/bank-accounts/{bank_account}', [\App\Http\Controllers\EmployeeBankAccountController::class, 'destroy'])->name('employees.bank-accounts.destroy');

    Route::resource('/setting', SettingController::class)->only(['index', 'update']); // Simplified Setting route

    // Leave Management
    Route::resource('/leave-balances', \App\Http\Controllers\LeaveBalanceController::class)->except(['show']);
    Route::resource('/leave-requests', \App\Http\Controllers\LeaveRequestController::class)->except(['show', 'edit', 'update']);

    // Attendance and Overtime
    Route::get('/attendance-records/import', [\App\Http\Controllers\AttendanceController::class, 'importForm'])->name('attendance-records.import-form');
    Route::post('/attendance-records/import', [\App\Http\Controllers\AttendanceController::class, 'import'])->name('attendance-records.import');
    Route::get('/attendance-records/import-status/{batchId}', [\App\Http\Controllers\AttendanceController::class, 'importStatus'])->name('attendance-records.import-status');
    Route::resource('/attendance-records', \App\Http\Controllers\AttendanceController::class)->except(['show']);
    Route::resource('/overtime-requests', \App\Http\Controllers\OvertimeRequestController::class)->except(['show']);

    // Payroll Calculation & Generation
    Route::resource('/payroll-runs', \App\Http\Controllers\PayrollRunController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/payslips/{payslip}', [\App\Http\Controllers\PayslipController::class, 'show'])->name('payslips.show');
    Route::get('/payslips/{payslip}/pdf', [\App\Http\Controllers\PayslipController::class, 'showPdf'])->name('payslips.pdf');
    Route::get('/reports/payroll-runs/{payrollRun}/ebupot', [\App\Http\Controllers\ReportController::class, 'exportEBupot'])->name('payroll-runs.ebupot');

    // Audit Logs
    Route::resource('audit-logs', \App\Http\Controllers\AuditLogController::class)->only(['index', 'show']);

    // Notifications
    Route::get('/notifications', [\App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/mark-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // Generic Approvals
    Route::post('/approvals/{type}/{id}/submit', [\App\Http\Controllers\ApprovalController::class, 'submit'])->name('approvals.submit');
    Route::post('/approvals/{type}/{id}/approve', [\App\Http\Controllers\ApprovalController::class, 'approve'])->name('approvals.approve');
    Route::post('/approvals/{type}/{id}/reject', [\App\Http\Controllers\ApprovalController::class, 'reject'])->name('approvals.reject');
    Route::post('/approvals/{type}/{id}/mark-paid', [\App\Http\Controllers\ApprovalController::class, 'markPaid'])->name('approvals.mark-paid');

    // Employee Loans
    Route::resource('/employee-loans', \App\Http\Controllers\EmployeeLoanController::class)->except(['edit', 'update', 'destroy']);
    Route::post('/employee-loans/{id}/disburse', [\App\Http\Controllers\EmployeeLoanController::class, 'disburse'])->name('employee-loans.disburse');
});
