<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'driver_id',
        'vehicle_id',
        'status',
        'start_time',
        'end_time',
        'start_location',
        'end_location',
        'distance',
        'fuel_consumption',
        'fuel_price',
        'fuel_cost',
        'insurance_cost',
        'maintenance_cost',
        'total_cost',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'distance' => 'decimal:2',
        'fuel_consumption' => 'decimal:2',
        'fuel_price' => 'decimal:2',
        'fuel_cost' => 'decimal:2',
        'insurance_cost' => 'decimal:2',
        'maintenance_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Check if driver is available for the given time period
     */
    public static function isDriverAvailable($driverId, $startTime, $endTime, $excludeTripId = null)
    {
        $query = self::where('driver_id', $driverId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($subQ) use ($startTime, $endTime) {
                      $subQ->where('start_time', '<=', $startTime)
                           ->where('end_time', '>=', $endTime);
                  });
            });

        if ($excludeTripId) {
            $query->where('id', '!=', $excludeTripId);
        }

        return $query->count() === 0;
    }

    /**
     * Check if vehicle is available for the given time period
     */
    public static function isVehicleAvailable($vehicleId, $startTime, $endTime, $excludeTripId = null)
    {
        $query = self::where('vehicle_id', $vehicleId)
            ->where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($subQ) use ($startTime, $endTime) {
                      $subQ->where('start_time', '<=', $startTime)
                           ->where('end_time', '>=', $endTime);
                  });
            });

        if ($excludeTripId) {
            $query->where('id', '!=', $excludeTripId);
        }

        return $query->count() === 0;
    }

    /**
     * Get active trips (in progress right now)
     */
    public static function getActiveTrips()
    {
        $now = Carbon::now();
        return self::where('status', 'in_progress')
            ->where('start_time', '<=', $now)
            ->where('end_time', '>=', $now)
            ->with(['driver', 'vehicle', 'company']);
    }

    /**
     * Get available drivers for a time period
     */
    public static function getAvailableDrivers($startTime, $endTime, $companyId = null)
    {
        $unavailableDriverIds = self::where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($subQ) use ($startTime, $endTime) {
                      $subQ->where('start_time', '<=', $startTime)
                           ->where('end_time', '>=', $endTime);
                  });
            })
            ->pluck('driver_id')
            ->toArray();

        $query = \App\Models\Driver::whereNotIn('id', $unavailableDriverIds);
        
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->with('company');
    }

    /**
     * Get available vehicles for a time period
     */
    public static function getAvailableVehicles($startTime, $endTime, $companyId = null)
    {
        $unavailableVehicleIds = self::where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($subQ) use ($startTime, $endTime) {
                      $subQ->where('start_time', '<=', $startTime)
                           ->where('end_time', '>=', $endTime);
                  });
            })
            ->pluck('vehicle_id')
            ->toArray();

        $query = \App\Models\Vehicle::whereNotIn('id', $unavailableVehicleIds);
        
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->with('company');
    }

    /**
     * Get trips completed this month
     */
    public static function getTripsCompletedThisMonth($companyId = null)
    {
        $query = self::where('status', 'completed')
            ->whereMonth('end_time', Carbon::now()->month)
            ->whereYear('end_time', Carbon::now()->year);

        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return $query->with(['driver', 'vehicle', 'company']);
    }
}
