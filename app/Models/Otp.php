<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Otp extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'otps';
    protected $softDelete = true;

    protected $hidden = ['deleted_at'];
}
