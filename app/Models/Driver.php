<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'company_id',
        'email',
        'phone',
        'address',
        'license_number',
        'license_expiry_date',
    ];

    protected $casts = [
        'license_expiry_date' => 'date', // or 'datetime' if you need time
    ];
    

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class);
    }

    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}
