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
function seedCompaniesDriversVehiclesD(): array {
    $companyA = Company::create(['name' => 'Company A', 'email' => 'a@comp.test']);
    $companyB = Company::create(['name' => 'Company B', 'email' => 'b@comp.test']);

    $driverA1 = Driver::create([
        'full_name' => 'Driver A1',
        'company_id' => $companyA->id,
        'email' => 'a1@comp.test',
        'license_number' => 'A1',
        'license_expiry_date' => '2030-01-01',
    ]);
    $driverA2 = Driver::create([
        'full_name' => 'Driver A2',
        'company_id' => $companyA->id,
        'email' => 'a2@comp.test',
        'license_number' => 'A2',
        'license_expiry_date' => '2030-01-01',
    ]);
    $driverB1 = Driver::create([
        'full_name' => 'Driver B1',
        'company_id' => $companyB->id,
        'email' => 'b1@comp.test',
        'license_number' => 'B1',
        'license_expiry_date' => '2030-01-01',
    ]);

    $vehicleA = Vehicle::create([
        'make' => 'Ford',
        'model' => 'Transit',
        'year' => 2022,
        'company_id' => $companyA->id,
        'capacity' => 2,
        'plate_number' => 'A-001',
        'vehicle_identification_number' => 'VIN-A-001',
    ]);

    $vehicleB = Vehicle::create([
        'make' => 'Mercedes',
        'model' => 'Sprinter',
        'year' => 2021,
        'company_id' => $companyB->id,
        'capacity' => 3,
        'plate_number' => 'B-001',
        'vehicle_identification_number' => 'VIN-B-001',
    ]);

    return compact('companyA','companyB','driverA1','driverA2','driverB1','vehicleA','vehicleB');
}

function createTripD(int $companyId, int $driverId, int $vehicleId, string $status, string $start, string $end): Trip {
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

test('returns all drivers when there are no overlapping trips', function () {
    $ctx = seedCompaniesDriversVehiclesD();

    $start = Carbon::parse('2025-04-01 10:00:00');
    $end   = Carbon::parse('2025-04-01 11:00:00');

    $drivers = Trip::getAvailableDrivers($start, $end)->pluck('id')->toArray();

    expect($drivers)->toContain($ctx['driverA1']->id)
        ->and($drivers)->toContain($ctx['driverA2']->id)
        ->and($drivers)->toContain($ctx['driverB1']->id)
        ->and($drivers)->toHaveCount(3);
});

test('excludes drivers with overlapping trips in the requested window', function () {
    $ctx = seedCompaniesDriversVehiclesD();

    // Overlapping trip for driverA1
    createTripD($ctx['companyA']->id, $ctx['driverA1']->id, $ctx['vehicleA']->id, 'in_progress', '2025-04-01 10:15:00', '2025-04-01 10:45:00');

    $start = Carbon::parse('2025-04-01 10:00:00');
    $end   = Carbon::parse('2025-04-01 11:00:00');

    $drivers = Trip::getAvailableDrivers($start, $end)->pluck('id')->toArray();

    expect($drivers)->not->toContain($ctx['driverA1']->id)
        ->and($drivers)->toContain($ctx['driverA2']->id)
        ->and($drivers)->toContain($ctx['driverB1']->id)
        ->and($drivers)->toHaveCount(2);
});

test('does not exclude drivers whose overlapping trips are cancelled', function () {
    $ctx = seedCompaniesDriversVehiclesD();

    // Cancelled overlapping trip for driverA1 -> should be ignored
    createTripD($ctx['companyA']->id, $ctx['driverA1']->id, $ctx['vehicleA']->id, 'cancelled', '2025-04-01 10:15:00', '2025-04-01 10:45:00');

    $start = Carbon::parse('2025-04-01 10:00:00');
    $end   = Carbon::parse('2025-04-01 11:00:00');

    $drivers = Trip::getAvailableDrivers($start, $end)->pluck('id')->toArray();

    expect($drivers)->toContain($ctx['driverA1']->id)
        ->and($drivers)->toContain($ctx['driverA2']->id)
        ->and($drivers)->toContain($ctx['driverB1']->id)
        ->and($drivers)->toHaveCount(3);
});

test('inclusive boundaries: trip ending or starting exactly at window edges excludes the driver', function () {
    $ctx = seedCompaniesDriversVehiclesD();

    // End exactly at start
    createTripD($ctx['companyA']->id, $ctx['driverA1']->id, $ctx['vehicleA']->id, 'in_progress', '2025-04-01 09:00:00', '2025-04-01 10:00:00');
    // Start exactly at end
    createTripD($ctx['companyA']->id, $ctx['driverA2']->id, $ctx['vehicleA']->id, 'in_progress', '2025-04-01 11:00:00', '2025-04-01 12:00:00');

    $start = Carbon::parse('2025-04-01 10:00:00');
    $end   = Carbon::parse('2025-04-01 11:00:00');

    $drivers = Trip::getAvailableDrivers($start, $end)->pluck('id')->toArray();

    // Because whereBetween is inclusive in the implementation, both are considered overlapping
    expect($drivers)->not->toContain($ctx['driverA1']->id)
        ->and($drivers)->not->toContain($ctx['driverA2']->id)
        ->and($drivers)->toContain($ctx['driverB1']->id)
        ->and($drivers)->toHaveCount(1);
});

test('scopes by companyId when provided', function () {
    $ctx = seedCompaniesDriversVehiclesD();

    // Create overlapping trip for company A driver A1
    createTripD($ctx['companyA']->id, $ctx['driverA1']->id, $ctx['vehicleA']->id, 'in_progress', '2025-04-01 10:15:00', '2025-04-01 10:45:00');

    $start = Carbon::parse('2025-04-01 10:00:00');
    $end   = Carbon::parse('2025-04-01 11:00:00');

    $driversA = Trip::getAvailableDrivers($start, $end, $ctx['companyA']->id)->pluck('id')->toArray();
    $driversB = Trip::getAvailableDrivers($start, $end, $ctx['companyB']->id)->pluck('id')->toArray();

    // For company A: A1 excluded, A2 included, B1 should not appear due to company scope
    expect($driversA)->not->toContain($ctx['driverA1']->id)
        ->and($driversA)->toContain($ctx['driverA2']->id)
        ->and($driversA)->toHaveCount(1);

    // For company B: only B1 appears and is available (no trips)
    expect($driversB)->toContain($ctx['driverB1']->id)
        ->and($driversB)->toHaveCount(1);
});

test('eager loads company relation on returned drivers', function () {
    $ctx = seedCompaniesDriversVehiclesD();
    $start = Carbon::parse('2025-04-01 10:00:00');
    $end   = Carbon::parse('2025-04-01 11:00:00');

    $drivers = Trip::getAvailableDrivers($start, $end)->get();
    $sample = $drivers->first();

    expect($sample->relationLoaded('company'))->toBeTrue();
});
