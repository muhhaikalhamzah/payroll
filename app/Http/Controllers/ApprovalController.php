<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PayrollRun;
use App\Models\LeaveRequest;
use Illuminate\Support\Facades\Gate;

class ApprovalController extends Controller
{
    protected function getModel($type, $id)
    {
        if ($type === 'payroll_run') {
            return PayrollRun::findOrFail($id);
        } elseif ($type === 'leave_request') {
            return LeaveRequest::findOrFail($id);
        }
        abort(404, 'Unknown approvable type');
    }

    public function submit(Request $request, $type, $id)
    {
        $model = $this->getModel($type, $id);

        if ($type === 'payroll_run') {
            Gate::authorize('submit-payroll-runs');
        } elseif ($type === 'leave_request') {
            Gate::authorize('submit-leave-requests');
            // Employee can only submit their own
            if ($model->employee->user_id !== auth()->id()) {
                abort(403, 'Unauthorized action.');
            }
        }

        $request->validate([
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $status = 'PENDING_FINANCE'; // default

            // Pessimistic locking to prevent race condition
            if ($type === 'payroll_run') {
                $model = PayrollRun::where('id', $id)->lockForUpdate()->firstOrFail();
                if ($model->status !== 'DRAFT') {
                    throw new \Exception('Only DRAFT can be submitted.');
                }
                $model->status = 'PENDING_FINANCE';
                $model->save();
            } elseif ($type === 'leave_request') {
                $model = LeaveRequest::where('id', $id)->lockForUpdate()->firstOrFail();
                if ($model->status !== 'DRAFT') {
                    throw new \Exception('Only DRAFT can be submitted.');
                }
                $model->status = 'PENDING_MANAGER';
                $model->save();
                $status = 'PENDING_MANAGER';
            }

            $model->submitForApproval($request->notes, $status);

            DB::commit();

            return back()->with('success', 'Submitted for approval successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(Request $request, $type, $id)
    {
        $model = $this->getModel($type, $id);

        if ($type === 'payroll_run') {
            Gate::authorize('approve-payroll-runs');
        } elseif ($type === 'leave_request') {
            Gate::authorize('approve-leave-requests');
        }

        $request->validate([
            'comments' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            $status = 'APPROVED'; // default

            if ($type === 'payroll_run') {
                $model = PayrollRun::where('id', $id)->lockForUpdate()->firstOrFail();
                if ($model->status !== 'PENDING_FINANCE') {
                    throw new \Exception('Only PENDING_FINANCE can be approved.');
                }
                $model->status = 'APPROVED';
                $model->save();
            } elseif ($type === 'leave_request') {
                $model = LeaveRequest::where('id', $id)->lockForUpdate()->firstOrFail();
                if ($model->status === 'PENDING_MANAGER') {
                    $model->status = 'PENDING_HR';
                    $status = 'PENDING_HR';
                } elseif ($model->status === 'PENDING_HR') {
                    $model->status = 'APPROVED';
                    $status = 'APPROVED';

                    // Deduction logic
                    $duration = \Carbon\Carbon::parse($model->start_date)->diffInDaysFiltered(function(\Carbon\Carbon $date) {
                        return !$date->isWeekend();
                    }, \Carbon\Carbon::parse($model->end_date)) + 1;

                    $balance = \App\Models\LeaveBalance::where('employee_id', $model->employee_id)
                        ->where('leave_type_id', $model->leave_type_id)
                        ->where('year', date('Y', strtotime($model->start_date)))
                        ->lockForUpdate()
                        ->first();
                    
                    if ($balance) {
                        $balance->used += $duration;
                        $balance->save();
                    }
                } else {
                    throw new \Exception('Invalid status for approval.');
                }
                $model->save();
            }

            $model->approveApproval(null, $request->comments, $status);

            DB::commit();

            return back()->with('success', 'Approved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, $type, $id)
    {
        $model = $this->getModel($type, $id);

        if ($type === 'payroll_run') {
            Gate::authorize('reject-payroll-runs');
        } elseif ($type === 'leave_request') {
            Gate::authorize('approve-leave-requests');
        }

        $request->validate([
            'comments' => 'required|string'
        ]);

        try {
            DB::beginTransaction();

            if ($type === 'payroll_run') {
                $model = PayrollRun::where('id', $id)->lockForUpdate()->firstOrFail();
                if ($model->status !== 'PENDING_FINANCE') {
                    throw new \Exception('Only PENDING_FINANCE can be rejected.');
                }
                $model->status = 'DRAFT'; // go back to draft if rejected
                $model->save();
            } elseif ($type === 'leave_request') {
                $model = LeaveRequest::where('id', $id)->lockForUpdate()->firstOrFail();
                if (!in_array($model->status, ['PENDING_MANAGER', 'PENDING_HR'])) {
                    throw new \Exception('Only pending requests can be rejected.');
                }
                $model->status = 'REJECTED';
                $model->save();
            }

            $model->rejectApproval(null, $request->comments);

            DB::commit();

            return back()->with('success', 'Rejected successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }

    public function markPaid(Request $request, $type, $id)
    {
        $model = $this->getModel($type, $id);

        if ($type === 'payroll_run') {
            Gate::authorize('mark-payroll-runs-paid');
        }

        try {
            DB::beginTransaction();

            if ($type === 'payroll_run') {
                $model = PayrollRun::where('id', $id)->lockForUpdate()->firstOrFail();
                if ($model->status !== 'APPROVED') {
                    throw new \Exception('Only APPROVED can be marked as paid.');
                }
                $model->status = 'PAID';
                $model->save();

                // Add log for PAID
                $approval = $model->latestApproval;
                if ($approval) {
                    $approval->logs()->create([
                        'actor_id' => \Illuminate\Support\Facades\Auth::id(),
                        'action' => 'PAID',
                        'comments' => 'Realized payment',
                    ]);
                }
            }

            DB::commit();

            return back()->with('success', 'Marked as PAID successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
