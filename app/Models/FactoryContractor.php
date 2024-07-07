<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FactoryContractor extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'factory_contractors';
    protected $softDelete = true;

    protected $hidden = ['password', 'deleted_at'];

    public function contractors()
    {
        return $this->belongsTo(Contractor::class);
    }
}
