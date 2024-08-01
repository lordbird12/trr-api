<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rain extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'rain';
    protected $softDelete = true;
    protected $casts = [
        'co_or_points' => 'array',
        'center' => 'array',
    ];

    protected $hidden = ['deleted_at'];
}
