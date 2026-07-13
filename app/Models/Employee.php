<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Auditable;

class Employee extends Model
{
    use HasFactory, Auditable, SoftDeletes;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function positionHistories()
    {
        return $this->hasMany(EmployeePositionHistory::class);
    }

    public function bankAccounts()
    {
        return $this->hasMany(EmployeeBankAccount::class);
    }

    public function salaryComponents()
    {
        return $this->belongsToMany(SalaryComponent::class, 'employee_salary_components')
            ->withPivot('amount')
            ->withTimestamps();
    }
    public function employeeLoans()
    {
        return $this->hasMany(EmployeeLoan::class);
    }

    public function payslips()
    {
        return $this->hasMany(Payslip::class);
    }

    public function getPtkpStatusAttribute()
    {
        return $this->marital_status . '/' . $this->dependents_count;
    }
}
