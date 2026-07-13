<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayslipComponent extends Model
{
    protected $fillable = ['payslip_id', 'name', 'amount', 'type', 'employee_loan_id'];

    public function payslip()
    {
        return $this->belongsTo(Payslip::class);
    }

    public function employeeLoan()
    {
        return $this->belongsTo(EmployeeLoan::class);
    }
}
