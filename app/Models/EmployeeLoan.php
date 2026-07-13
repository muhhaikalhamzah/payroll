<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeLoan extends Model
{
    protected $fillable = ['employee_id', 'total_amount', 'remaining_amount', 'monthly_installment'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
