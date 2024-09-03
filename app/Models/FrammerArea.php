<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FrammerArea extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'frammer_areas';
    protected $softDelete = true;
    
    protected $casts = [
        'co_or_points' => 'array',
        'center' => 'array',
    ];
    protected $hidden = ['deleted_at'];
}
