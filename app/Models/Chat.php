<?php

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Chat extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'chat';
    protected $softDelete = true;

    protected $hidden = ['deleted_at'];

    //////////////////////////////////////// format //////////////////////////////////////

    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('d/m/Y H:i:s');
    }

    //////////////////////////////////////// relation //////////////////////////////////////

    public function frammer()
    {
        return $this->belongsTo(Frammers::class, 'qouta_id', 'qouta_id');
    }

    public function chat_msgs()
    {
        return $this->hasMany(Chat_msg::class);
    }
}
