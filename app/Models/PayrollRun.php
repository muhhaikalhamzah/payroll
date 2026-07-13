<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Approvable;
use App\Traits\Auditable;

class PayrollRun extends Model
{
    use HasFactory, Approvable, Auditable;

    protected $fillable = ['type', 'period_month', 'period_year', 'status', 'created_by'];

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function isLockedByPaid($employeeId, $date)
    {
        $month = date('n', strtotime($date));
        $year = date('Y', strtotime($date));
        
        return self::where('status', 'PAID')
            ->where('period_month', $month)
            ->where('period_year', $year)
            ->whereHas('payslips', function($q) use ($employeeId) {
                $q->where('employee_id', $employeeId);
            })->exists();
    }
}
