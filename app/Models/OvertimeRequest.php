<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class OvertimeRequest extends Model
{
    use HasFactory, Auditable;

    protected $fillable = [
        'employee_id',
        'date',
        'duration_minutes',
        'reason',
        'status',
        'approved_by',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
