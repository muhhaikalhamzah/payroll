<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BpjsRecord extends Model
{
    protected $fillable = ['payslip_id', 'jht_amount', 'jp_amount', 'jkk_amount', 'jkm_amount', 'kesehatan_amount'];

    public function payslip()
    {
        return $this->belongsTo(Payslip::class);
    }
}
