<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IncomePaid extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $table = 'income_paids';
    protected $softDelete = true;

    protected $hidden = ['deleted_at'];

    public function contractors()
    {
        return $this->belongsTo(IncomeType::class);
    }
}
