<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxTerRate extends Model
{
    use HasFactory;

    protected $fillable = ['kategori', 'no_lapisan', 'batas_bawah', 'batas_atas', 'tarif'];
}
