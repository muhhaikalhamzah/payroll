<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use App\Models\Employee;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class OvertimeRequestController extends Controller
{
    public function index()
    {
        Gate::authorize('viewAny', OvertimeRequest::class);
        $overtime_requests = OvertimeRequest::with(['employee', 'approver'])->orderBy('date', 'desc')->get();
        
        // Filter by user's employee ID if they don't have manage/approve perms
        if (!Auth::user()->hasPermission('manage-overtime-requests') && !Auth::user()->hasPermission('approve-overtime-requests')) {
            $overtime_requests = $overtime_requests->where('employee_id', Auth::user()->employee?->id);
        }

        $title = 'Overtime Requests';
        return view('overtime-requests.index', compact('overtime_requests', 'title'));
    }

    public function create()
    {
        Gate::authorize('create', OvertimeRequest::class);
        $title = 'Create Overtime Request';
        $employees = Employee::all();
        return view('overtime-requests.create', compact('employees', 'title'));
    }

    public function store(Request $request)
    {
        Gate::authorize('create', OvertimeRequest::class);
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'duration_minutes' => 'required|integer|min:1',
            'reason' => 'required|string',
            'status' => 'required|in:DRAFT,PENDING_MANAGER',
        ]);

        // Cross-module validation: Ensure attendance exists for that date
        $attendanceExists = AttendanceRecord::where('employee_id', $validated['employee_id'])
            ->where('date', $validated['date'])
            ->exists();

        if (!$attendanceExists) {
            return redirect()->back()->withInput()->with('error', 'Cannot request overtime for a date without an attendance record.');
        }

        OvertimeRequest::create($validated);
        return redirect()->route('overtime-requests.index')->with('success', 'Overtime request created successfully.');
    }

    public function edit(OvertimeRequest $overtime_request)
    {
        Gate::authorize('update', $overtime_request);
        $title = 'Edit Overtime Request';
        $employees = Employee::all();
        return view('overtime-requests.edit', compact('overtime_request', 'employees', 'title'));
    }

    public function update(Request $request, OvertimeRequest $overtime_request)
    {
        Gate::authorize('update', $overtime_request);
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date' => 'required|date',
            'duration_minutes' => 'required|integer|min:1',
            'reason' => 'required|string',
            'status' => 'required|in:DRAFT,PENDING_MANAGER,APPROVED,REJECTED',
        ]);

        // Cross-module validation: Ensure attendance exists for that date
        $attendanceExists = AttendanceRecord::where('employee_id', $validated['employee_id'])
            ->where('date', $validated['date'])
            ->exists();

        if (!$attendanceExists) {
            return redirect()->back()->withInput()->with('error', 'Cannot request overtime for a date without an attendance record.');
        }

        // Logic for approver
        if (in_array($validated['status'], ['APPROVED', 'REJECTED']) && $overtime_request->status !== $validated['status']) {
            if ($overtime_request->status !== 'PENDING_MANAGER') {
                return redirect()->back()->with('error', 'Only PENDING_MANAGER requests can be approved or rejected.');
            }
            if (!Auth::user()->hasPermission('approve-overtime-requests') && !Auth::user()->hasPermission('manage-overtime-requests')) {
                return redirect()->back()->with('error', 'Unauthorized to approve or reject overtime.');
            }
            $validated['approved_by'] = Auth::id();
        }

        $overtime_request->update($validated);
        return redirect()->route('overtime-requests.index')->with('success', 'Overtime request updated successfully.');
    }

    public function destroy(OvertimeRequest $overtime_request)
    {
        Gate::authorize('delete', $overtime_request);
        $overtime_request->delete();
        return redirect()->route('overtime-requests.index')->with('success', 'Overtime request deleted successfully.');
    }
}
