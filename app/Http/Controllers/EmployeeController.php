<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;

class EmployeeController extends Controller
{
    public function index()
    {
        Gate::authorize('view-employees');

        $employees = Employee::with(['department', 'position', 'user'])->latest()->get();

        return view('employees.index', [
            'title' => 'Employees',
            'employees' => $employees,
        ]);
    }

    public function create()
    {
        Gate::authorize('manage-employees');

        return view('employees.create', [
            'title' => 'Add Employee',
            'departments' => Department::all(),
            'positions' => Position::all(),
            // Users without employee profile
            'unlinkedUsers' => User::doesntHave('employee')->get(),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-employees');

        $validated = $request->validate([
            'nik' => ['required', 'string', 'regex:/^[0-9]{16}$/', 'unique:employees,nik'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:employees,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'hire_date' => ['required', 'date'],
            'department_id' => ['required', 'exists:departments,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'status' => ['required', 'in:active,resign'],
            
            // User account options
            'user_action' => ['required', 'in:none,link_existing,create_new'],
            'existing_user_id' => ['nullable', 'required_if:user_action,link_existing', 'exists:users,id'],
            'new_user_email' => ['nullable', 'required_if:user_action,create_new', 'email', 'unique:users,email'],
            'new_user_password' => ['nullable', 'required_if:user_action,create_new', 'min:8'],
            'new_user_role_id' => ['nullable', 'required_if:user_action,create_new', 'exists:roles,id'],
        ]);

        DB::transaction(function () use ($validated) {
            $userId = null;

            if ($validated['user_action'] === 'link_existing') {
                $userId = $validated['existing_user_id'];
            } elseif ($validated['user_action'] === 'create_new') {
                $user = User::create([
                    'name' => $validated['first_name'] . ' ' . $validated['last_name'],
                    'email' => $validated['new_user_email'],
                    'password' => Hash::make($validated['new_user_password']),
                    'role_id' => $validated['new_user_role_id'],
                ]);
                $userId = $user->id;
            }

            $employee = Employee::create([
                'nik' => $validated['nik'],
                'user_id' => $userId,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'hire_date' => $validated['hire_date'],
                'department_id' => $validated['department_id'],
                'position_id' => $validated['position_id'],
                'status' => $validated['status'],
            ]);

            // Create position history
            $employee->positionHistories()->create([
                'position_id' => $employee->position_id,
                'start_date' => $employee->hire_date,
            ]);

            // Create Audit Log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'CREATED',
                'auditable_type' => Employee::class,
                'auditable_id' => $employee->id,
                'new_values' => $employee->toJson(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        return redirect()->route('employees.index')->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee)
    {
        Gate::authorize('view-employees');

        $employee->load(['department', 'position', 'user', 'positionHistories.position', 'bankAccounts', 'salaryComponents']);

        return view('employees.show', [
            'title' => 'Employee Details',
            'employee' => $employee,
        ]);
    }

    public function edit(Employee $employee)
    {
        Gate::authorize('manage-employees');

        return view('employees.edit', [
            'title' => 'Edit Employee',
            'employee' => $employee,
            'departments' => Department::all(),
            'positions' => Position::all(),
            'unlinkedUsers' => User::doesntHave('employee')->orWhere('id', $employee->user_id)->get(),
        ]);
    }

    public function update(Request $request, Employee $employee)
    {
        Gate::authorize('manage-employees');

        $validated = $request->validate([
            'nik' => ['required', 'string', 'regex:/^[0-9]{16}$/', 'unique:employees,nik,' . $employee->id],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:employees,email,' . $employee->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'hire_date' => ['required', 'date'],
            'department_id' => ['required', 'exists:departments,id'],
            'position_id' => ['required', 'exists:positions,id'],
            'status' => ['required', 'in:active,resign'],
            
            // Existing user link option
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        DB::transaction(function () use ($validated, $employee) {
            $oldValues = $employee->toJson();
            $oldPositionId = $employee->position_id;

            $employee->update([
                'nik' => $validated['nik'],
                'user_id' => $validated['user_id'] ?? null,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'hire_date' => $validated['hire_date'],
                'department_id' => $validated['department_id'],
                'position_id' => $validated['position_id'],
                'status' => $validated['status'],
            ]);

            // Handle position history logic if position changed
            if ($oldPositionId != $validated['position_id']) {
                // Close the old history
                $activeHistory = $employee->positionHistories()->whereNull('end_date')->first();
                if ($activeHistory) {
                    $activeHistory->update(['end_date' => now()]);
                }

                // Create new history
                $employee->positionHistories()->create([
                    'position_id' => $validated['position_id'],
                    'start_date' => now(),
                ]);
            }

            // Create Audit Log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'UPDATED',
                'auditable_type' => Employee::class,
                'auditable_id' => $employee->id,
                'old_values' => $oldValues,
                'new_values' => $employee->toJson(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        return redirect()->route('employees.index')->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        Gate::authorize('manage-employees');

        DB::transaction(function () use ($employee) {
            $oldValues = $employee->toJson();

            $employee->delete();

            // Create Audit Log
            AuditLog::create([
                'user_id' => auth()->id(),
                'action' => 'DELETED',
                'auditable_type' => Employee::class,
                'auditable_id' => $employee->id,
                'old_values' => $oldValues,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });

        return redirect()->route('employees.index')->with('success', 'Employee archived (soft deleted) successfully.');
    }
}
