<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaxRecord extends Model
{
    protected $fillable = ['payslip_id', 'ter_category', 'bruto_amount', 'pph21_amount'];

    public function payslip()
    {
        return $this->belongsTo(Payslip::class);
    }
}
