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

        $overtimeRequest = OvertimeRequest::create($validated);

        if ($validated['status'] === 'PENDING_MANAGER') {
            $managers = \App\Models\User::whereHas('role', function($q){ $q->where('slug', 'manager'); })->get();
            \Illuminate\Support\Facades\Notification::send($managers, new \App\Notifications\StatusChangedNotification(
                "Overtime Request Pending",
                "An overtime request by " . ($overtimeRequest->employee->user->name ?? 'Employee') . " requires approval."
            ));
        }

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

        if (\App\Models\PayrollRun::isLockedByPaid($overtime_request->employee_id, $overtime_request->date)) {
            $month = date('n', strtotime($overtime_request->date));
            $year = date('Y', strtotime($overtime_request->date));
            return back()->with('error', "Data ini tidak bisa diubah karena sudah tercakup dalam payroll yang telah dibayarkan periode {$month}-{$year}");
        }

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

        $oldStatus = $overtime_request->status;
        $overtime_request->update($validated);

        if ($oldStatus !== $validated['status']) {
            if ($validated['status'] === 'PENDING_MANAGER') {
                $managers = \App\Models\User::whereHas('role', function($q){ $q->where('slug', 'manager'); })->get();
                \Illuminate\Support\Facades\Notification::send($managers, new \App\Notifications\StatusChangedNotification(
                    "Overtime Request Pending",
                    "An overtime request by " . ($overtime_request->employee->user->name ?? 'Employee') . " requires approval."
                ));
            } elseif (in_array($validated['status'], ['APPROVED', 'REJECTED'])) {
                if ($overtime_request->employee && $overtime_request->employee->user) {
                    $overtime_request->employee->user->notify(new \App\Notifications\StatusChangedNotification(
                        "Overtime Request " . ucfirst(strtolower($validated['status'])),
                        "Your overtime request has been " . strtolower($validated['status']) . "."
                    ));
                }
            }
        }

        return redirect()->route('overtime-requests.index')->with('success', 'Overtime request updated successfully.');
    }

    public function destroy(OvertimeRequest $overtime_request)
    {
        Gate::authorize('delete', $overtime_request);

        if (\App\Models\PayrollRun::isLockedByPaid($overtime_request->employee_id, $overtime_request->date)) {
            $month = date('n', strtotime($overtime_request->date));
            $year = date('Y', strtotime($overtime_request->date));
            return back()->with('error', "Data ini tidak bisa dihapus karena sudah tercakup dalam payroll yang telah dibayarkan periode {$month}-{$year}");
        }

        $overtime_request->delete();
        return redirect()->route('overtime-requests.index')->with('success', 'Overtime request deleted successfully.');
    }
}
