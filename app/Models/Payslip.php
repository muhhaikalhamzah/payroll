<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class Payslip extends Model
{
    use HasFactory, Auditable;

    protected $fillable = ['payroll_run_id', 'employee_id', 'basic_salary', 'total_allowances', 'total_deductions', 'net_pay', 'status', 'needs_intervention', 'intervention_reason'];

    public function payrollRun()
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function components()
    {
        return $this->hasMany(PayslipComponent::class);
    }

    public function taxRecord()
    {
        return $this->hasOne(TaxRecord::class);
    }

    public function bpjsRecord()
    {
        return $this->hasOne(BpjsRecord::class);
    }
}
