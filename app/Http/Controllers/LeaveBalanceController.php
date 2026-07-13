<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\LeaveBalance;
use App\Models\Employee;
use App\Models\LeaveType;
use Illuminate\Support\Facades\Gate;

class LeaveBalanceController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('manage-leave-balances');
        $title = 'Leave Balances';
        $year = $request->input('year', date('Y'));
        $leaveBalances = LeaveBalance::with(['employee', 'leaveType'])->where('year', $year)->get();
        return view('leave-balances.index', compact('title', 'leaveBalances', 'year'));
    }

    public function create()
    {
        Gate::authorize('manage-leave-balances');
        $title = 'Add Leave Balance';
        $employees = Employee::where('status', 'active')->get();
        $leaveTypes = LeaveType::all();
        return view('leave-balances.create', compact('title', 'employees', 'leaveTypes'));
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-leave-balances');
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'year' => 'required|integer|min:2000|max:2100',
            'balance' => 'required|integer|min:0',
            'used' => 'required|integer|min:0'
        ]);

        LeaveBalance::updateOrCreate(
            [
                'employee_id' => $validated['employee_id'],
                'leave_type_id' => $validated['leave_type_id'],
                'year' => $validated['year']
            ],
            [
                'balance' => $validated['balance'],
                'used' => $validated['used']
            ]
        );

        return redirect()->route('leave-balances.index')->with('success', 'Leave Balance saved successfully.');
    }

    public function edit(LeaveBalance $leaveBalance)
    {
        Gate::authorize('manage-leave-balances');
        $title = 'Edit Leave Balance';
        $employees = Employee::all();
        $leaveTypes = LeaveType::all();
        return view('leave-balances.edit', compact('title', 'leaveBalance', 'employees', 'leaveTypes'));
    }

    public function update(Request $request, LeaveBalance $leaveBalance)
    {
        Gate::authorize('manage-leave-balances');
        $validated = $request->validate([
            'balance' => 'required|integer|min:0',
            'used' => 'required|integer|min:0'
        ]);

        $leaveBalance->update($validated);

        return redirect()->route('leave-balances.index')->with('success', 'Leave Balance updated successfully.');
    }

    public function destroy(LeaveBalance $leaveBalance)
    {
        Gate::authorize('manage-leave-balances');
        $leaveBalance->delete();
        return redirect()->route('leave-balances.index')->with('success', 'Leave Balance deleted successfully.');
    }
}
