<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MonthlyConsumption extends Model
{
    //
        protected $fillable = [
            'meter_id',
            'month',
            'consumption_value',
        ];
    public function meter()
    {
        return $this->belongsTo(Meter::class);
    }
}
