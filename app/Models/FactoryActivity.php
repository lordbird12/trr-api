<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FactoryActivity extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'factory_activity';
    protected $softDelete = true;

    protected $hidden = ['deleted_at'];
}
