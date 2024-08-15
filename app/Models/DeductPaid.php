<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeductPaid extends Model
{
    use HasFactory;

    use SoftDeletes;

    protected $table = 'deduct_paids';
    protected $softDelete = true;
    protected $fillable = [
        // other attributes...
        'frammer_id',
        // other attributes...
    ];
    protected $hidden = ['deleted_at'];

    public function deduct_types()
    {
        return $this->belongsTo(DeductType::class);
    }

    public function factory_activity()
    {
        return $this->belongsTo(FactoryActivity::class, "factory_activity_id");
    }
}
