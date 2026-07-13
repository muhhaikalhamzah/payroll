<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayslipComponent extends Model
{
    protected $fillable = ['payslip_id', 'name', 'amount', 'type'];

    public function payslip()
    {
        return $this->belongsTo(Payslip::class);
    }
}
