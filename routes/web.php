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
    
    // Employee Data Management
    Route::resource('/employees', \App\Http\Controllers\EmployeeController::class);
    Route::post('/employees/{employee}/bank-accounts', [\App\Http\Controllers\EmployeeBankAccountController::class, 'store'])->name('employees.bank-accounts.store');
    Route::put('/employees/{employee}/bank-accounts/{bank_account}', [\App\Http\Controllers\EmployeeBankAccountController::class, 'update'])->name('employees.bank-accounts.update');
    Route::delete('/employees/{employee}/bank-accounts/{bank_account}', [\App\Http\Controllers\EmployeeBankAccountController::class, 'destroy'])->name('employees.bank-accounts.destroy');

    Route::resource('/setting', SettingController::class)->only(['index', 'update']); // Simplified Setting route

    // Attendance and Overtime
    Route::get('/attendance-records/import', [\App\Http\Controllers\AttendanceController::class, 'importForm'])->name('attendance-records.import-form');
    Route::post('/attendance-records/import', [\App\Http\Controllers\AttendanceController::class, 'import'])->name('attendance-records.import');
    Route::get('/attendance-records/import-status/{batchId}', [\App\Http\Controllers\AttendanceController::class, 'importStatus'])->name('attendance-records.import-status');
    Route::resource('/attendance-records', \App\Http\Controllers\AttendanceController::class)->except(['show']);
    Route::resource('/overtime-requests', \App\Http\Controllers\OvertimeRequestController::class)->except(['show']);

    // Payroll Calculation & Generation
    Route::resource('/payroll-runs', \App\Http\Controllers\PayrollRunController::class)->only(['index', 'create', 'store', 'show']);
    Route::get('/payslips/{payslip}', [\App\Http\Controllers\PayslipController::class, 'show'])->name('payslips.show');
});
