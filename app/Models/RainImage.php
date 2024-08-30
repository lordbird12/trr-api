<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RainImage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'rain_image';
    protected $softDelete = true;

    protected $hidden = ['deleted_at'];
}
