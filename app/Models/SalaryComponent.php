<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryComponent extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'type', 'is_fixed', 'is_taxable'];
    
    protected $casts = [
        'is_fixed' => 'boolean',
        'is_taxable' => 'boolean',
    ];
}
