<?php

use App\Models\Company;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Helper to create a basic company, driver, and vehicle for tests.
 */
function seedBasicEntities(): array {
    $company = Company::create([
        'name' => 'Acme Logistics',
        'email' => 'info@acme.test',
        'website' => null,
        'phone' => null,
        'address' => null,
    ]);

    $driver = Driver::create([
        'full_name' => 'John Doe',
        'company_id' => $company->id,
        'email' => 'john.doe@acme.test',
        'phone' => null,
        'address' => null,
        'license_number' => 'LIC123',
        'license_expiry_date' => '2030-01-01',
    ]);

    $vehicle = Vehicle::create([
        'make' => 'Ford',
        'model' => 'Transit',
        'year' => 2022,
        'company_id' => $company->id,
        'capacity' => 2,
        'plate_number' => 'TEST-001',
        'vehicle_identification_number' => 'VIN1234567890',
    ]);

    return [$company, $driver, $vehicle];
}

/**
 * Helper to create a trip for a given driver/vehicle/time.
 */
function makeTrip(int $companyId, int $driverId, int $vehicleId, string|Carbon $start, string|Carbon $end, string $status = 'pending'): Trip {
    return Trip::create([
        'company_id' => $companyId,
        'driver_id' => $driverId,
        'vehicle_id' => $vehicleId,
        'status' => $status,
        'start_time' => $start instanceof Carbon ? $start : Carbon::parse($start),
        'end_time' => $end instanceof Carbon ? $end : Carbon::parse($end),
        'start_location' => 'A',
        'end_location' => 'B',
    ]);
}

test('driver is available when there are no trips in the period', function () {
    [$company, $driver, $vehicle] = seedBasicEntities();

    $start = Carbon::parse('2025-01-01 10:00:00');
    $end = Carbon::parse('2025-01-01 11:00:00');

    expect(Trip::isDriverAvailable($driver->id, $start, $end))->toBeTrue();
});

test('driver is not available when an existing trip overlaps at the start', function () {
    [$company, $driver, $vehicle] = seedBasicEntities();

    makeTrip($company->id, $driver->id, $vehicle->id, '2025-01-01 09:30:00', '2025-01-01 10:30:00');

    $start = Carbon::parse('2025-01-01 10:00:00');
    $end = Carbon::parse('2025-01-01 11:00:00');

    expect(Trip::isDriverAvailable($driver->id, $start, $end))->toBeFalse();
});

test('driver is not available when an existing trip overlaps at the end', function () {
    [$company, $driver, $vehicle] = seedBasicEntities();

    makeTrip($company->id, $driver->id, $vehicle->id, '2025-01-01 10:30:00', '2025-01-01 11:30:00');

    $start = Carbon::parse('2025-01-01 10:00:00');
    $end = Carbon::parse('2025-01-01 11:00:00');

    expect(Trip::isDriverAvailable($driver->id, $start, $end))->toBeFalse();
});

test('driver is not available when an existing trip fully covers the requested window', function () {
    [$company, $driver, $vehicle] = seedBasicEntities();

    makeTrip($company->id, $driver->id, $vehicle->id, '2025-01-01 09:00:00', '2025-01-01 12:00:00');

    $start = Carbon::parse('2025-01-01 10:00:00');
    $end = Carbon::parse('2025-01-01 11:00:00');

    expect(Trip::isDriverAvailable($driver->id, $start, $end))->toBeFalse();
});

test('driver is available when overlapping trips are cancelled', function () {
    [$company, $driver, $vehicle] = seedBasicEntities();

    makeTrip($company->id, $driver->id, $vehicle->id, '2025-01-01 10:15:00', '2025-01-01 10:45:00', status: 'cancelled');

    $start = Carbon::parse('2025-01-01 10:00:00');
    $end = Carbon::parse('2025-01-01 11:00:00');

    expect(Trip::isDriverAvailable($driver->id, $start, $end))->toBeTrue();
});

test('excludeTripId ignores the provided trip when checking availability', function () {
    [$company, $driver, $vehicle] = seedBasicEntities();

    $trip = makeTrip($company->id, $driver->id, $vehicle->id, '2025-01-01 10:15:00', '2025-01-01 10:45:00');

    $start = Carbon::parse('2025-01-01 10:00:00');
    $end = Carbon::parse('2025-01-01 11:00:00');

    // Without exclusion -> unavailable
    expect(Trip::isDriverAvailable($driver->id, $start, $end))->toBeFalse();

    // With exclusion -> available
    expect(Trip::isDriverAvailable($driver->id, $start, $end, $trip->id))->toBeTrue();
});

test('due to inclusive boundaries, a trip ending exactly at requested start makes driver unavailable', function () {
    [$company, $driver, $vehicle] = seedBasicEntities();

    makeTrip($company->id, $driver->id, $vehicle->id, '2025-01-01 09:00:00', '2025-01-01 10:00:00');

    $start = Carbon::parse('2025-01-01 10:00:00');
    $end = Carbon::parse('2025-01-01 11:00:00');

    // whereBetween is inclusive in the current implementation, thus considered overlapping
    expect(Trip::isDriverAvailable($driver->id, $start, $end))->toBeFalse();
});
