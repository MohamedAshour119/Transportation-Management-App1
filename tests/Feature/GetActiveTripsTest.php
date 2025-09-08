<?php

use App\Models\Company;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Local helpers (unique names for this file)
 */
function seedEntitiesA(): array {
    $company = Company::create([
        'name' => 'Wayne Logistics',
        'email' => 'contact@wayne.test',
    ]);

    $driver = Driver::create([
        'full_name' => 'Bruce Wayne',
        'company_id' => $company->id,
        'email' => 'bruce@wayne.test',
        'license_number' => 'BAT-001',
        'license_expiry_date' => '2030-01-01',
    ]);

    $vehicle = Vehicle::create([
        'make' => 'Tesla',
        'model' => 'Semi',
        'year' => 2023,
        'company_id' => $company->id,
        'capacity' => 2,
        'plate_number' => 'GOTHAM-1',
        'vehicle_identification_number' => 'VIN-ACTIVE-1',
    ]);

    return [$company, $driver, $vehicle];
}

function createTrip(int $companyId, int $driverId, int $vehicleId, string $status, string $start, string $end): Trip {
    return Trip::create([
        'company_id' => $companyId,
        'driver_id' => $driverId,
        'vehicle_id' => $vehicleId,
        'status' => $status,
        'start_time' => Carbon::parse($start),
        'end_time' => Carbon::parse($end),
        'start_location' => 'Origin',
        'end_location' => 'Destination',
    ]);
}

test('getActiveTrips returns in_progress trips happening right now (basic)', function () {
    [$company, $driver, $vehicle] = seedEntitiesA();
    Carbon::setTestNow('2025-03-01 10:00:00');

    // Active trip spanning now
    $active = createTrip($company->id, $driver->id, $vehicle->id, 'in_progress', '2025-03-01 09:00:00', '2025-03-01 11:00:00');
    // Future trip (not started yet)
    createTrip($company->id, $driver->id, $vehicle->id, 'in_progress', '2025-03-01 10:30:00', '2025-03-01 12:00:00');
    // Past trip (already ended)
    createTrip($company->id, $driver->id, $vehicle->id, 'in_progress', '2025-03-01 08:00:00', '2025-03-01 09:30:00');

    $found = Trip::getActiveTrips()->get();
    expect($found)->toHaveCount(1)
        ->and($found->first()->id)->toBe($active->id);
});

test('getActiveTrips respects inclusive boundaries at start and end', function () {
    [$company, $driver, $vehicle] = seedEntitiesA();
    Carbon::setTestNow('2025-03-01 10:00:00');

    // Exactly matches now at start boundary
    $atStart = createTrip($company->id, $driver->id, $vehicle->id, 'in_progress', '2025-03-01 10:00:00', '2025-03-01 10:30:00');
    // Exactly matches now at end boundary
    $atEnd = createTrip($company->id, $driver->id, $vehicle->id, 'in_progress', '2025-03-01 09:30:00', '2025-03-01 10:00:00');
    // Outside (future)
    createTrip($company->id, $driver->id, $vehicle->id, 'in_progress', '2025-03-01 10:01:00', '2025-03-01 10:10:00');
    // Outside (past)
    createTrip($company->id, $driver->id, $vehicle->id, 'in_progress', '2025-03-01 08:00:00', '2025-03-01 09:59:59');

    $foundIds = Trip::getActiveTrips()->pluck('id')->toArray();
    expect($foundIds)->toContain($atStart->id)
        ->and($foundIds)->toContain($atEnd->id)
        ->and($foundIds)->toHaveCount(2);
});

test('getActiveTrips filters by status = in_progress only', function () {
    [$company, $driver, $vehicle] = seedEntitiesA();
    Carbon::setTestNow('2025-03-01 10:00:00');

    // These span now, but statuses vary
    createTrip($company->id, $driver->id, $vehicle->id, 'pending', '2025-03-01 09:00:00', '2025-03-01 11:00:00');
    createTrip($company->id, $driver->id, $vehicle->id, 'completed', '2025-03-01 09:00:00', '2025-03-01 11:00:00');
    createTrip($company->id, $driver->id, $vehicle->id, 'cancelled', '2025-03-01 09:00:00', '2025-03-01 11:00:00');
    createTrip($company->id, $driver->id, $vehicle->id, 'scheduled', '2025-03-01 09:00:00', '2025-03-01 11:00:00');

    $active = createTrip($company->id, $driver->id, $vehicle->id, 'in_progress', '2025-03-01 09:30:00', '2025-03-01 10:30:00');

    $found = Trip::getActiveTrips()->get();
    expect($found)->toHaveCount(1)
        ->and($found->first()->id)->toBe($active->id);
});

test('getActiveTrips eager loads driver, vehicle, and company', function () {
    [$company, $driver, $vehicle] = seedEntitiesA();
    Carbon::setTestNow('2025-03-01 10:00:00');

    createTrip($company->id, $driver->id, $vehicle->id, 'in_progress', '2025-03-01 09:30:00', '2025-03-01 10:30:00');

    $found = Trip::getActiveTrips()->get();
    $trip = $found->first();

    // Assert relations are eager-loaded
    expect($trip->relationLoaded('driver'))->toBeTrue()
        ->and($trip->relationLoaded('vehicle'))->toBeTrue()
        ->and($trip->relationLoaded('company'))->toBeTrue();
});
