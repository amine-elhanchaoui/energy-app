<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reading extends Model
{
    //
    protected $fillable = [
        'meter_id',
        'date',
        'value',
        'photo_path',
    ];
    public function meter()
    {
        return $this->belongsTo(Meter::class);
    }
}
