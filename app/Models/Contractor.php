<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contractor extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $table = 'contractors';
    protected $softDelete = true;

    protected $hidden = ['password', 'deleted_at'];

    public function facetories_contractors()
    {
        return $this->hasMany(FactoryContractor::class);
    }

    public function feature_contractors()
    {
        return $this->hasMany(FeatureContractor::class);
    }
}
