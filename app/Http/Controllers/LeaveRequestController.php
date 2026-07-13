<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Gate;

class LeaveRequestController extends Controller
{
    public function index()
    {
        $title = 'Leave Requests';
        $user = auth()->user();

        if ($user->hasRole('super-admin') || $user->hasRole('admin')) {
            $leaveRequests = LeaveRequest::with(['employee.user', 'leaveType', 'approvals.logs'])->orderBy('created_at', 'desc')->get();
        } else {
            $employee = $user->employee;
            if (!$employee) {
                abort(403, 'User has no employee record.');
            }
            $leaveRequests = LeaveRequest::with(['employee.user', 'leaveType', 'approvals.logs'])
                ->where('employee_id', $employee->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('leave-requests.index', compact('title', 'leaveRequests'));
    }

    public function create()
    {
        Gate::authorize('submit-leave-requests');
        $title = 'Apply for Leave';
        $leaveTypes = LeaveType::all();
        $employee = auth()->user()->employee;
        if (!$employee) {
            return back()->with('error', 'You must be linked to an employee profile.');
        }

        // Fetch user's current balances
        $balances = LeaveBalance::where('employee_id', $employee->id)
            ->where('year', date('Y'))
            ->get();

        return view('leave-requests.create', compact('title', 'leaveTypes', 'balances', 'employee'));
    }

    public function store(Request $request)
    {
        Gate::authorize('submit-leave-requests');
        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string'
        ]);

        $employee = auth()->user()->employee;
        if (!$employee) {
            abort(403, 'Unauthorized.');
        }

        $duration = Carbon::parse($validated['start_date'])->diffInDaysFiltered(function(Carbon $date) {
            return !$date->isWeekend();
        }, Carbon::parse($validated['end_date'])) + 1;

        $balance = LeaveBalance::where('employee_id', $employee->id)
            ->where('leave_type_id', $validated['leave_type_id'])
            ->where('year', date('Y', strtotime($validated['start_date'])))
            ->first();

        if (!$balance) {
            return back()->withInput()->with('error', 'No leave balance configured for this type and year.');
        }

        // On-hold balance check: consider pending leaves
        $pendingDuration = LeaveRequest::where('employee_id', $employee->id)
            ->where('leave_type_id', $validated['leave_type_id'])
            ->whereIn('status', ['DRAFT', 'PENDING_MANAGER', 'PENDING_HR'])
            ->get()
            ->sum(function($req) {
                return Carbon::parse($req->start_date)->diffInDaysFiltered(function(Carbon $date) {
                    return !$date->isWeekend();
                }, Carbon::parse($req->end_date)) + 1;
            });

        $available = $balance->balance - $balance->used - $pendingDuration;

        if ($duration > $available) {
            return back()->withInput()->with('error', "Insufficient balance. You requested $duration days, but only have $available days available (including pending requests).");
        }

        // Overlap check
        $overlap = LeaveRequest::where('employee_id', $employee->id)
            ->whereIn('status', ['DRAFT', 'PENDING_MANAGER', 'PENDING_HR', 'APPROVED'])
            ->where(function($q) use ($validated) {
                $q->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                  ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                  ->orWhere(function($query) use ($validated) {
                      $query->where('start_date', '<=', $validated['start_date'])
                            ->where('end_date', '>=', $validated['end_date']);
                  });
            })->exists();

        if ($overlap) {
            return back()->withInput()->with('error', 'You already have a leave request overlapping these dates.');
        }

        LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $validated['leave_type_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'],
            'status' => 'DRAFT'
        ]);

        return redirect()->route('leave-requests.index')->with('success', 'Leave request created. You can submit it for approval.');
    }

    public function destroy(LeaveRequest $leaveRequest)
    {
        Gate::authorize('submit-leave-requests');
        if ($leaveRequest->employee->user_id !== auth()->id()) {
            abort(403);
        }
        if ($leaveRequest->status !== 'DRAFT') {
            return back()->with('error', 'Only DRAFT requests can be deleted.');
        }

        $leaveRequest->delete();
        return back()->with('success', 'Leave request deleted.');
    }
}
