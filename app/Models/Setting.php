<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable([
    'app_name',
    'copyright',
    'login_title',
    'keywords',
    'description',
    'logo'
])]

class Setting extends Model
{
    //
}
