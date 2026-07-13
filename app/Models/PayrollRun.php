<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollRun extends Model
{
    protected $fillable = ['period_month', 'period_year', 'status', 'created_by'];

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
