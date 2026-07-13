<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $fillable = [
        'approvable_type',
        'approvable_id',
        'status',
        'approver_id',
        'notes',
    ];

    public function approvable()
    {
        return $this->morphTo();
    }

    public function logs()
    {
        return $this->hasMany(ApprovalLog::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
