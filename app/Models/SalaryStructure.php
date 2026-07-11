<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryStructure extends Model
{
    use HasFactory;

    protected $fillable = ['position_id', 'base_salary'];

    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}
