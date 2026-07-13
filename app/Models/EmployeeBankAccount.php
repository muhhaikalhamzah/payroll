<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\Auditable;

class EmployeeBankAccount extends Model
{
    use HasFactory, Auditable;

    protected $guarded = ['id'];

    protected $casts = [
        'account_number' => 'encrypted',
        'is_primary' => 'boolean'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
