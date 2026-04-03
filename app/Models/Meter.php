<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meter extends Model
{
    //
    protected $fillable = [
        'user_id',
        'quarter_id',
        'name',
        'type',
        'location',
        'unit',
    ];
 
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function readings()
    {
        return $this->hasMany(Reading::class);
    }

    public function monthlyConsumptions()
    {
        return $this->hasMany(MonthlyConsumption::class);
    }

    public function quartier()
    {
        return $this->belongsTo(Quartier::class);
    }

}
