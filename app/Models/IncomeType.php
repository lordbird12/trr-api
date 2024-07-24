<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncomeType extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $table = 'income_types';
    protected $softDelete = true;

    protected $hidden = ['deleted_at'];

    public function income_paids()
    {
        return $this->hasMany(IncomePaid::class);
    }
}
