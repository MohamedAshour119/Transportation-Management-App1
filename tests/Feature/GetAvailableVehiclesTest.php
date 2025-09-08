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
function seedCompaniesDriversVehiclesV(): array {
    $companyA = Company::create(['name' => 'Company A', 'email' => 'a@comp.test']);
    $companyB = Company::create(['name' => 'Company B', 'email' => 'b@comp.test']);

    $driverA = Driver::create([
        'full_name' => 'Driver A',
        'company_id' => $companyA->id,
        'email' => 'da@comp.test',
        'license_number' => 'DA',
        'license_expiry_date' => '2030-01-01',
    ]);
    $driverB = Driver::create([
        'full_name' => 'Driver B',
        'company_id' => $companyB->id,
        'email' => 'db@comp.test',
        'license_number' => 'DB',
        'license_expiry_date' => '2030-01-01',
    ]);

    $vehicleA1 = Vehicle::create([
        'make' => 'Ford',
        'model' => 'Transit',
        'year' => 2022,
        'company_id' => $companyA->id,
        'capacity' => 2,
        'plate_number' => 'A-001',
        'vehicle_identification_number' => 'VIN-A-001',
    ]);
    $vehicleA2 = Vehicle::create([
        'make' => 'Ford',
        'model' => 'Transit',
        'year' => 2022,
        'company_id' => $companyA->id,
        'capacity' => 2,
        'plate_number' => 'A-002',
        'vehicle_identification_number' => 'VIN-A-002',
    ]);
    $vehicleB1 = Vehicle::create([
        'make' => 'Mercedes',
        'model' => 'Sprinter',
        'year' => 2021,
        'company_id' => $companyB->id,
        'capacity' => 3,
        'plate_number' => 'B-001',
        'vehicle_identification_number' => 'VIN-B-001',
    ]);

    return compact('companyA','companyB','driverA','driverB','vehicleA1','vehicleA2','vehicleB1');
}

function createTripV2(int $companyId, int $driverId, int $vehicleId, string $status, string $start, string $end): Trip {
    return Trip::create([
        'company_id' => $companyId,
        'driver_id' => $driverId,
        'vehicle_id' => $vehicleId,
        'status' => $status,
        'start_time' => Carbon::parse($start),
        'end_time' => Carbon::parse($end),
        'start_location' => 'Start',
        'end_location' => 'End',
    ]);
}

test('returns all vehicles when there are no overlapping trips', function () {
    $ctx = seedCompaniesDriversVehiclesV();

    $start = Carbon::parse('2025-05-01 10:00:00');
    $end   = Carbon::parse('2025-05-01 11:00:00');

    $vehicles = Trip::getAvailableVehicles($start, $end)->pluck('id')->toArray();

    expect($vehicles)->toContain($ctx['vehicleA1']->id)
        ->and($vehicles)->toContain($ctx['vehicleA2']->id)
        ->and($vehicles)->toContain($ctx['vehicleB1']->id)
        ->and($vehicles)->toHaveCount(3);
});

test('excludes vehicles with overlapping trips in the requested window', function () {
    $ctx = seedCompaniesDriversVehiclesV();

    // Overlapping trip for vehicleA1
    createTripV2($ctx['companyA']->id, $ctx['driverA']->id, $ctx['vehicleA1']->id, 'in_progress', '2025-05-01 10:15:00', '2025-05-01 10:45:00');

    $start = Carbon::parse('2025-05-01 10:00:00');
    $end   = Carbon::parse('2025-05-01 11:00:00');

    $vehicles = Trip::getAvailableVehicles($start, $end)->pluck('id')->toArray();

    expect($vehicles)->not->toContain($ctx['vehicleA1']->id)
        ->and($vehicles)->toContain($ctx['vehicleA2']->id)
        ->and($vehicles)->toContain($ctx['vehicleB1']->id)
        ->and($vehicles)->toHaveCount(2);
});

test('does not exclude vehicles whose overlapping trips are cancelled', function () {
    $ctx = seedCompaniesDriversVehiclesV();

    // Cancelled overlapping trip for vehicleA1 -> should be ignored
    createTripV2($ctx['companyA']->id, $ctx['driverA']->id, $ctx['vehicleA1']->id, 'cancelled', '2025-05-01 10:15:00', '2025-05-01 10:45:00');

    $start = Carbon::parse('2025-05-01 10:00:00');
    $end   = Carbon::parse('2025-05-01 11:00:00');

    $vehicles = Trip::getAvailableVehicles($start, $end)->pluck('id')->toArray();

    expect($vehicles)->toContain($ctx['vehicleA1']->id)
        ->and($vehicles)->toContain($ctx['vehicleA2']->id)
        ->and($vehicles)->toContain($ctx['vehicleB1']->id)
        ->and($vehicles)->toHaveCount(3);
});

test('inclusive boundaries: trip ending or starting exactly at window edges excludes the vehicle', function () {
    $ctx = seedCompaniesDriversVehiclesV();

    // End exactly at start
    createTripV2($ctx['companyA']->id, $ctx['driverA']->id, $ctx['vehicleA1']->id, 'in_progress', '2025-05-01 09:00:00', '2025-05-01 10:00:00');
    // Start exactly at end
    createTripV2($ctx['companyA']->id, $ctx['driverA']->id, $ctx['vehicleA2']->id, 'in_progress', '2025-05-01 11:00:00', '2025-05-01 12:00:00');

    $start = Carbon::parse('2025-05-01 10:00:00');
    $end   = Carbon::parse('2025-05-01 11:00:00');

    $vehicles = Trip::getAvailableVehicles($start, $end)->pluck('id')->toArray();

    // Because whereBetween is inclusive in the implementation, both are considered overlapping
    expect($vehicles)->not->toContain($ctx['vehicleA1']->id)
        ->and($vehicles)->not->toContain($ctx['vehicleA2']->id)
        ->and($vehicles)->toContain($ctx['vehicleB1']->id)
        ->and($vehicles)->toHaveCount(1);
});

test('scopes by companyId when provided', function () {
    $ctx = seedCompaniesDriversVehiclesV();

    // Create overlapping trip for company A vehicle A1
    createTripV2($ctx['companyA']->id, $ctx['driverA']->id, $ctx['vehicleA1']->id, 'in_progress', '2025-05-01 10:15:00', '2025-05-01 10:45:00');

    $start = Carbon::parse('2025-05-01 10:00:00');
    $end   = Carbon::parse('2025-05-01 11:00:00');

    $vehiclesA = Trip::getAvailableVehicles($start, $end, $ctx['companyA']->id)->pluck('id')->toArray();
    $vehiclesB = Trip::getAvailableVehicles($start, $end, $ctx['companyB']->id)->pluck('id')->toArray();

    // For company A: A1 excluded, A2 included, B1 should not appear due to company scope
    expect($vehiclesA)->not->toContain($ctx['vehicleA1']->id)
        ->and($vehiclesA)->toContain($ctx['vehicleA2']->id)
        ->and($vehiclesA)->toHaveCount(1);

    // For company B: only B1 appears and is available (no trips)
    expect($vehiclesB)->toContain($ctx['vehicleB1']->id)
        ->and($vehiclesB)->toHaveCount(1);
});

test('eager loads company relation on returned vehicles', function () {
    $ctx = seedCompaniesDriversVehiclesV();
    $start = Carbon::parse('2025-05-01 10:00:00');
    $end   = Carbon::parse('2025-05-01 11:00:00');

    $vehicles = Trip::getAvailableVehicles($start, $end)->get();
    $sample = $vehicles->first();

    expect($sample->relationLoaded('company'))->toBeTrue();
});
