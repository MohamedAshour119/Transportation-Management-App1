<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Vehicle extends Model
{
    protected $fillable = [
        'make',
        'model',
        'year',
        'company_id',
        'capacity',
        'plate_number',
        'vehicle_identification_number'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function drivers()
    {
        return $this->belongsToMany(Driver::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}
