<?php

use App\Models\Company;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Helpers local to this test file
 */
function seedCompaniesAndEntities(): array {
    $companyA = Company::create([
        'name' => 'Alpha Logistics',
        'email' => 'alpha@example.test',
    ]);

    $companyB = Company::create([
        'name' => 'Beta Transport',
        'email' => 'beta@example.test',
    ]);

    $driverA = Driver::create([
        'full_name' => 'Alice Alpha',
        'company_id' => $companyA->id,
        'email' => 'alice@alpha.test',
        'license_number' => 'ALP-001',
        'license_expiry_date' => '2030-01-01',
    ]);

    $vehicleA = Vehicle::create([
        'make' => 'Volvo',
        'model' => 'FH',
        'year' => 2024,
        'company_id' => $companyA->id,
        'capacity' => 3,
        'plate_number' => 'ALPHA-1',
        'vehicle_identification_number' => 'VIN-ALPHA-1',
    ]);

    $driverB = Driver::create([
        'full_name' => 'Bob Beta',
        'company_id' => $companyB->id,
        'email' => 'bob@beta.test',
        'license_number' => 'BET-001',
        'license_expiry_date' => '2030-01-01',
    ]);

    $vehicleB = Vehicle::create([
        'make' => 'Scania',
        'model' => 'R-Series',
        'year' => 2023,
        'company_id' => $companyB->id,
        'capacity' => 2,
        'plate_number' => 'BETA-1',
        'vehicle_identification_number' => 'VIN-BETA-1',
    ]);

    return [$companyA, $companyB, $driverA, $driverB, $vehicleA, $vehicleB];
}

function makeTripForMonthTests(int $companyId, int $driverId, int $vehicleId, string $status, string $endTime, ?string $startTime = null): Trip {
    return Trip::create([
        'company_id' => $companyId,
        'driver_id' => $driverId,
        'vehicle_id' => $vehicleId,
        'status' => $status,
        'start_time' => $startTime ? Carbon::parse($startTime) : Carbon::parse($endTime)->subHours(2),
        'end_time' => Carbon::parse($endTime),
        'start_location' => 'Warehouse',
        'end_location' => 'Client Site',
    ]);
}

test('getTripsCompletedThisMonth returns only completed trips with end_time in the current month', function () {
    [$companyA, $companyB, $driverA, $driverB, $vehicleA, $vehicleB] = seedCompaniesAndEntities();
    Carbon::setTestNow('2025-03-15 10:00:00');

    // In current month and completed
    $t1 = makeTripForMonthTests($companyA->id, $driverA->id, $vehicleA->id, 'completed', '2025-03-10 12:00:00');
    $t2 = makeTripForMonthTests($companyB->id, $driverB->id, $vehicleB->id, 'completed', '2025-03-01 08:00:00');

    // Different statuses in current month (should be excluded)
    makeTripForMonthTests($companyA->id, $driverA->id, $vehicleA->id, 'pending', '2025-03-11 09:00:00');
    makeTripForMonthTests($companyA->id, $driverA->id, $vehicleA->id, 'in_progress', '2025-03-12 09:00:00');
    makeTripForMonthTests($companyA->id, $driverA->id, $vehicleA->id, 'cancelled', '2025-03-13 09:00:00');

    // Completed but different month/year (should be excluded)
    makeTripForMonthTests($companyA->id, $driverA->id, $vehicleA->id, 'completed', '2025-02-28 18:00:00');
    makeTripForMonthTests($companyA->id, $driverA->id, $vehicleA->id, 'completed', '2024-03-20 18:00:00');

    $found = Trip::getTripsCompletedThisMonth()->get();
    $foundIds = $found->pluck('id')->toArray();

    expect($found)->toHaveCount(2)
        ->and($foundIds)->toContain($t1->id)
        ->and($foundIds)->toContain($t2->id);
});

test('getTripsCompletedThisMonth includes boundary days (first and last day of month)', function () {
    [$companyA, $companyB, $driverA, $driverB, $vehicleA, $vehicleB] = seedCompaniesAndEntities();
    Carbon::setTestNow('2025-03-15 10:00:00');

    // First day of month at midnight and last day at last second
    $first = makeTripForMonthTests($companyA->id, $driverA->id, $vehicleA->id, 'completed', '2025-03-01 00:00:00');
    $last  = makeTripForMonthTests($companyB->id, $driverB->id, $vehicleB->id, 'completed', '2025-03-31 23:59:59');

    // Neighbors just outside month
    makeTripForMonthTests($companyA->id, $driverA->id, $vehicleA->id, 'completed', '2025-02-28 23:59:59');
    makeTripForMonthTests($companyA->id, $driverA->id, $vehicleA->id, 'completed', '2025-04-01 00:00:00');

    $foundIds = Trip::getTripsCompletedThisMonth()->pluck('id')->toArray();
    expect($foundIds)->toContain($first->id)
        ->and($foundIds)->toContain($last->id)
        ->and($foundIds)->toHaveCount(2);
});

test('getTripsCompletedThisMonth filters by company when $companyId is provided', function () {
    [$companyA, $companyB, $driverA, $driverB, $vehicleA, $vehicleB] = seedCompaniesAndEntities();
    Carbon::setTestNow('2025-03-15 10:00:00');

    $a1 = makeTripForMonthTests($companyA->id, $driverA->id, $vehicleA->id, 'completed', '2025-03-05 10:00:00');
    makeTripForMonthTests($companyB->id, $driverB->id, $vehicleB->id, 'completed', '2025-03-06 10:00:00');
    makeTripForMonthTests($companyA->id, $driverA->id, $vehicleA->id, 'completed', '2025-02-28 10:00:00'); // outside month

    $foundForA = Trip::getTripsCompletedThisMonth($companyA->id)->get();
    $idsForA = $foundForA->pluck('id')->toArray();

    expect($idsForA)->toContain($a1->id)
        ->and($foundForA)->toHaveCount(1);
});

test('getTripsCompletedThisMonth eager loads driver, vehicle, and company', function () {
    [$companyA, $companyB, $driverA, $driverB, $vehicleA, $vehicleB] = seedCompaniesAndEntities();
    Carbon::setTestNow('2025-03-15 10:00:00');

    makeTripForMonthTests($companyA->id, $driverA->id, $vehicleA->id, 'completed', '2025-03-10 12:00:00');

    $trip = Trip::getTripsCompletedThisMonth()->first();
    expect($trip->relationLoaded('driver'))->toBeTrue()
        ->and($trip->relationLoaded('vehicle'))->toBeTrue()
        ->and($trip->relationLoaded('company'))->toBeTrue();
});
