<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Approval extends Model
{
    use HasFactory, Auditable;

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
