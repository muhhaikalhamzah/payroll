<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Approvable;
use App\Traits\Auditable;

class PayrollRun extends Model
{
    use HasFactory, Approvable, Auditable;

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
