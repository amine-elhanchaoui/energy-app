<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quartier extends Model
{
    //
    protected $fillable = [
        'city_id',
        'name',
    ];
    public function city()
    {
        return $this->belongsTo(City::class);
    }
    public function meters()
    {
        return $this->hasMany(Meter::class);
    }

}
