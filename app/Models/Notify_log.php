<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notify_log extends Model
{
    use HasFactory;
     use SoftDeletes;

    protected $table = 'notify_log';
    protected $softDelete = true;

    protected $hidden = ['deleted_at'];

    //////////////////////////////////////// format //////////////////////////////////////

     // protected function serializeDate(DateTimeInterface $date)
    // {
    //     return $date->format('Y-m-d H:i:s');
    // }

    //////////////////////////////////////// relation //////////////////////////////////////

}
