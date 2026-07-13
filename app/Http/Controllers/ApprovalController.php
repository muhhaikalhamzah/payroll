<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PayrollRun;
use Illuminate\Support\Facades\Gate;

class ApprovalController extends Controller
{
    protected function getModel($type, $id)
    {
        if ($type === 'payroll_run') {
            return PayrollRun::findOrFail($id);
        }
        abort(404, 'Unknown approvable type');
    }

    public function submit(Request $request, $type, $id)
    {
        $model = $this->getModel($type, $id);

        if ($type === 'payroll_run') {
            Gate::authorize('submit-payroll-runs');
        }

        $request->validate([
            'notes' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Pessimistic locking to prevent race condition
            if ($type === 'payroll_run') {
                $model = PayrollRun::where('id', $id)->lockForUpdate()->firstOrFail();
                if ($model->status !== 'DRAFT') {
                    throw new \Exception('Only DRAFT can be submitted.');
                }
                $model->status = 'PENDING_FINANCE';
                $model->save();
            }

            $model->submitForApproval($request->notes);

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
        }

        $request->validate([
            'comments' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            if ($type === 'payroll_run') {
                $model = PayrollRun::where('id', $id)->lockForUpdate()->firstOrFail();
                if ($model->status !== 'PENDING_FINANCE') {
                    throw new \Exception('Only PENDING_FINANCE can be approved.');
                }
                $model->status = 'APPROVED';
                $model->save();
            }

            $model->approveApproval(null, $request->comments);

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
