<?php

namespace App\Traits;

use App\Models\Approval;
use Illuminate\Support\Facades\Auth;

trait Approvable
{
    public function approvals()
    {
        return $this->morphMany(Approval::class, 'approvable');
    }

    public function latestApproval()
    {
        return $this->morphOne(Approval::class, 'approvable')->latestOfMany();
    }

    public function submitForApproval($notes = null, $status = 'PENDING_FINANCE')
    {
        $approval = $this->approvals()->create([
            'status' => $status,
            'notes' => $notes,
        ]);

        $approval->logs()->create([
            'actor_id' => Auth::id(),
            'action' => 'SUBMITTED',
            'comments' => $notes,
        ]);

        return $approval;
    }

    public function approveApproval($approvalId = null, $comments = null, $status = 'APPROVED')
    {
        $approval = $approvalId ? $this->approvals()->find($approvalId) : $this->latestApproval;
        
        if ($approval) {
            $approval->update([
                'status' => $status,
                'approver_id' => Auth::id(),
            ]);

            $approval->logs()->create([
                'actor_id' => Auth::id(),
                'action' => 'APPROVED',
                'comments' => $comments,
            ]);
        }
        return $approval;
    }

    public function rejectApproval($approvalId = null, $comments = null, $status = 'REJECTED')
    {
        $approval = $approvalId ? $this->approvals()->find($approvalId) : $this->latestApproval;
        
        if ($approval) {
            $approval->update([
                'status' => $status,
                'approver_id' => Auth::id(),
            ]);

            $approval->logs()->create([
                'actor_id' => Auth::id(),
                'action' => 'REJECTED',
                'comments' => $comments,
            ]);
        }
        return $approval;
    }
}
