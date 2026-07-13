<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalLog extends Model
{
    protected $fillable = [
        'approval_id',
        'actor_id',
        'action',
        'comments',
    ];

    public function approval()
    {
        return $this->belongsTo(Approval::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
