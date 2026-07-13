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

    public function submitForApproval($notes = null)
    {
        $approval = $this->approvals()->create([
            'status' => 'PENDING_FINANCE',
            'notes' => $notes,
        ]);

        $approval->logs()->create([
            'actor_id' => Auth::id(),
            'action' => 'SUBMITTED',
            'comments' => $notes,
        ]);

        return $approval;
    }

    public function approveApproval($approvalId = null, $comments = null)
    {
        $approval = $approvalId ? $this->approvals()->find($approvalId) : $this->latestApproval;
        
        if ($approval) {
            $approval->update([
                'status' => 'APPROVED',
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

    public function rejectApproval($approvalId = null, $comments = null)
    {
        $approval = $approvalId ? $this->approvals()->find($approvalId) : $this->latestApproval;
        
        if ($approval) {
            $approval->update([
                'status' => 'REJECTED',
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
