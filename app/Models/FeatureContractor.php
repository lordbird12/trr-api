<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeatureContractor extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'feature_contractors';
    protected $softDelete = true;

    protected $hidden = ['password', 'deleted_at'];

    public function contractors()
    {
        return $this->belongsTo(Contractor::class);
    }
}
