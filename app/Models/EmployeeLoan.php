<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Approvable;
use App\Traits\Auditable;
use Illuminate\Notifications\Notifiable;

class EmployeeLoan extends Model
{
    use Approvable, Auditable, Notifiable;

    protected $fillable = [
        'employee_id', 
        'request_date', 
        'reason', 
        'total_amount', 
        'requested_tenor_months', 
        'monthly_installment', 
        'remaining_balance', 
        'status', 
        'approved_by', 
        'approved_at',
        'disbursed_by',
        'disbursed_at'
    ];

    protected $casts = [
        'request_date' => 'date',
        'approved_at' => 'datetime',
        'disbursed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function disburser()
    {
        return $this->belongsTo(User::class, 'disbursed_by');
    }

    /**
     * Helper to calculate monthly installment rounding up to the nearest integer.
     */
    public static function calculateMonthlyInstallment($totalAmount, $tenorMonths)
    {
        if ($tenorMonths <= 0) return 0;
        return ceil($totalAmount / $tenorMonths);
    }
}
