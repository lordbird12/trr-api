<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeductType extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $table = 'income_types';
    protected $softDelete = true;

    protected $hidden = ['deleted_at'];

    public function deduct_paids()
    {
        return $this->hasMany(DeductPaid::class);
    }
}
