<?php

use App\Models\Company;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Local helpers with unique names to avoid collisions across test files
 */
function seedEntitiesV(): array {
    $company = Company::create([
        'name' => 'Globex',
        'email' => 'ops@globex.test',
        'website' => null,
        'phone' => null,
        'address' => null,
    ]);

    $driver = Driver::create([
        'full_name' => 'Jane Roe',
        'company_id' => $company->id,
        'email' => 'jane.roe@globex.test',
        'phone' => null,
        'address' => null,
        'license_number' => 'LIC999',
        'license_expiry_date' => '2030-01-01',
    ]);

    $vehicle = Vehicle::create([
        'make' => 'Mercedes',
        'model' => 'Sprinter',
        'year' => 2021,
        'company_id' => $company->id,
        'capacity' => 3,
        'plate_number' => 'PLT-999',
        'vehicle_identification_number' => 'VIN-999',
    ]);

    return [$company, $driver, $vehicle];
}

function makeTripV(int $companyId, int $driverId, int $vehicleId, string|Carbon $start, string|Carbon $end, string $status = 'pending'): Trip {
    return Trip::create([
        'company_id' => $companyId,
        'driver_id' => $driverId,
        'vehicle_id' => $vehicleId,
        'status' => $status,
        'start_time' => $start instanceof Carbon ? $start : Carbon::parse($start),
        'end_time' => $end instanceof Carbon ? $end : Carbon::parse($end),
        'start_location' => 'X',
        'end_location' => 'Y',
    ]);
}

test('vehicle is available when there are no trips in the period', function () {
    [$company, $driver, $vehicle] = seedEntitiesV();

    $start = Carbon::parse('2025-02-01 10:00:00');
    $end = Carbon::parse('2025-02-01 11:00:00');

    expect(Trip::isVehicleAvailable($vehicle->id, $start, $end))->toBeTrue();
});

test('vehicle is not available when an existing trip overlaps at the start', function () {
    [$company, $driver, $vehicle] = seedEntitiesV();

    makeTripV($company->id, $driver->id, $vehicle->id, '2025-02-01 09:30:00', '2025-02-01 10:30:00');

    $start = Carbon::parse('2025-02-01 10:00:00');
    $end = Carbon::parse('2025-02-01 11:00:00');

    expect(Trip::isVehicleAvailable($vehicle->id, $start, $end))->toBeFalse();
});

test('vehicle is not available when an existing trip overlaps at the end', function () {
    [$company, $driver, $vehicle] = seedEntitiesV();

    makeTripV($company->id, $driver->id, $vehicle->id, '2025-02-01 10:30:00', '2025-02-01 11:30:00');

    $start = Carbon::parse('2025-02-01 10:00:00');
    $end = Carbon::parse('2025-02-01 11:00:00');

    expect(Trip::isVehicleAvailable($vehicle->id, $start, $end))->toBeFalse();
});

test('vehicle is not available when an existing trip fully covers the requested window', function () {
    [$company, $driver, $vehicle] = seedEntitiesV();

    makeTripV($company->id, $driver->id, $vehicle->id, '2025-02-01 09:00:00', '2025-02-01 12:00:00');

    $start = Carbon::parse('2025-02-01 10:00:00');
    $end = Carbon::parse('2025-02-01 11:00:00');

    expect(Trip::isVehicleAvailable($vehicle->id, $start, $end))->toBeFalse();
});

test('vehicle is available when overlapping trips are cancelled', function () {
    [$company, $driver, $vehicle] = seedEntitiesV();

    makeTripV($company->id, $driver->id, $vehicle->id, '2025-02-01 10:15:00', '2025-02-01 10:45:00', status: 'cancelled');

    $start = Carbon::parse('2025-02-01 10:00:00');
    $end = Carbon::parse('2025-02-01 11:00:00');

    expect(Trip::isVehicleAvailable($vehicle->id, $start, $end))->toBeTrue();
});

test('excludeTripId ignores the provided trip when checking vehicle availability', function () {
    [$company, $driver, $vehicle] = seedEntitiesV();

    $trip = makeTripV($company->id, $driver->id, $vehicle->id, '2025-02-01 10:15:00', '2025-02-01 10:45:00');

    $start = Carbon::parse('2025-02-01 10:00:00');
    $end = Carbon::parse('2025-02-01 11:00:00');

    // Without exclusion -> unavailable
    expect(Trip::isVehicleAvailable($vehicle->id, $start, $end))->toBeFalse();

    // With exclusion -> available
    expect(Trip::isVehicleAvailable($vehicle->id, $start, $end, $trip->id))->toBeTrue();
});

test('inclusive boundaries: a trip ending exactly at requested start makes vehicle unavailable', function () {
    [$company, $driver, $vehicle] = seedEntitiesV();

    makeTripV($company->id, $driver->id, $vehicle->id, '2025-02-01 09:00:00', '2025-02-01 10:00:00');

    $start = Carbon::parse('2025-02-01 10:00:00');
    $end = Carbon::parse('2025-02-01 11:00:00');

    // whereBetween is inclusive in the current implementation, thus considered overlapping
    expect(Trip::isVehicleAvailable($vehicle->id, $start, $end))->toBeFalse();
});
