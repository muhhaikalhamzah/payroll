<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'parent_department_id'];

    public function parent()
    {
        return $this->belongsTo(Department::class, 'parent_department_id');
    }

    public function children()
    {
        return $this->hasMany(Department::class, 'parent_department_id');
    }

    public function positions()
    {
        return $this->hasMany(Position::class);
    }
}
