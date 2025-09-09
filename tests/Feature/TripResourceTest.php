<?php

use App\Models\Trip;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Models\User;
use Livewire\Livewire;
use App\Filament\Resources\TripResource;
use App\Filament\Resources\TripResource\Pages\CreateTrip;
use App\Filament\Resources\TripResource\Pages\EditTrip;
use App\Filament\Resources\TripResource\Pages\ListTrips;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('can render trip list page', function () {
    $this->get(TripResource::getUrl('index'))->assertSuccessful();
});

test('can render trip create page', function () {
    $this->get(TripResource::getUrl('create'))->assertSuccessful();
});

test('can create trip', function () {
    $company = Company::factory()->create();
    $driver = Driver::factory()->create(['company_id' => $company->id]);
    $vehicle = Vehicle::factory()->create(['company_id' => $company->id]);
    
    $tripData = [
        'company_id' => $company->id,
        'driver_id' => $driver->id,
        'vehicle_id' => $vehicle->id,
        'status' => 'pending',
        'start_time' => now()->addHour()->format('Y-m-d H:i:s'),
        'end_time' => now()->addHours(3)->format('Y-m-d H:i:s'),
        'start_location' => 'Start Location',
        'end_location' => 'End Location',
        'distance' => 100.50,
        'fuel_consumption' => 15.75,
        'fuel_price' => 1.50,
        'fuel_cost' => 23.63,
        'insurance_cost' => 50.00,
        'maintenance_cost' => 25.00,
        'total_cost' => 98.63,
    ];

    Livewire::test(CreateTrip::class)
        ->fillForm($tripData)
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Trip::class, [
        'company_id' => $tripData['company_id'],
        'driver_id' => $tripData['driver_id'],
        'vehicle_id' => $tripData['vehicle_id'],
        'status' => $tripData['status'],
        'start_location' => $tripData['start_location'],
        'end_location' => $tripData['end_location'],
    ]);
});

test('can validate trip creation', function () {
    Livewire::test(CreateTrip::class)
        ->fillForm([
            'company_id' => null,
            'driver_id' => null,
            'vehicle_id' => null,
            'start_time' => '',
            'end_time' => '',
            'start_location' => '',
            'end_location' => '',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'company_id' => 'required',
            'driver_id' => 'required',
            'vehicle_id' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'start_location' => 'required',
            'end_location' => 'required',
        ]);
});

test('can validate end time is after start time', function () {
    $company = Company::factory()->create();
    $driver = Driver::factory()->create(['company_id' => $company->id]);
    $vehicle = Vehicle::factory()->create(['company_id' => $company->id]);
    
    $startTime = now()->addHour();
    $endTime = now(); // End time before start time

    Livewire::test(CreateTrip::class)
        ->fillForm([
            'company_id' => $company->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'start_time' => $startTime->format('Y-m-d H:i:s'),
            'end_time' => $endTime->format('Y-m-d H:i:s'),
            'start_location' => 'Start Location',
            'end_location' => 'End Location',
        ])
        ->call('create')
        ->assertHasFormErrors(['end_time' => 'after']);
});

test('can render trip edit page', function () {
    $trip = Trip::factory()->create();

    $this->get(TripResource::getUrl('edit', ['record' => $trip]))->assertSuccessful();
});

test('can retrieve trip data for editing', function () {
    $trip = Trip::factory()->create();

    Livewire::test(EditTrip::class, ['record' => $trip->getRouteKey()])
        ->assertFormSet([
            'company_id' => $trip->company_id,
            'driver_id' => $trip->driver_id,
            'vehicle_id' => $trip->vehicle_id,
            'status' => $trip->status,
            'start_time' => $trip->start_time->format('Y-m-d H:i:s'),
            'end_time' => $trip->end_time->format('Y-m-d H:i:s'),
            'start_location' => $trip->start_location,
            'end_location' => $trip->end_location,
            'distance' => $trip->distance,
            'fuel_consumption' => $trip->fuel_consumption,
            'fuel_price' => $trip->fuel_price,
            'fuel_cost' => $trip->fuel_cost,
            'insurance_cost' => $trip->insurance_cost,
            'maintenance_cost' => $trip->maintenance_cost,
            'total_cost' => $trip->total_cost,
        ]);
});

test('can update trip', function () {
    $trip = Trip::factory()->create();
    
    $newData = [
        'company_id' => $trip->company_id,
        'driver_id' => $trip->driver_id,
        'vehicle_id' => $trip->vehicle_id,
        'status' => 'in_progress',
        'start_time' => now()->addHour()->format('Y-m-d H:i:s'),
        'end_time' => now()->addHours(4)->format('Y-m-d H:i:s'),
        'start_location' => 'Updated Start Location',
        'end_location' => 'Updated End Location',
        'distance' => 200.75,
        'fuel_consumption' => 25.50,
        'fuel_price' => 1.75,
        'fuel_cost' => 44.63,
        'insurance_cost' => 75.00,
        'maintenance_cost' => 35.00,
        'total_cost' => 154.63,
    ];

    Livewire::test(EditTrip::class, ['record' => $trip->getRouteKey()])
        ->fillForm($newData)
        ->call('save')
        ->assertHasNoFormErrors();

    expect($trip->refresh())
        ->status->toBe($newData['status'])
        ->start_location->toBe($newData['start_location'])
        ->end_location->toBe($newData['end_location'])
        ->distance->toBe((string) $newData['distance'])
        ->fuel_consumption->toBe(number_format($newData['fuel_consumption'], 2, '.', ''))
        ->fuel_price->toBe(number_format($newData['fuel_price'], 2, '.', ''))
        ->insurance_cost->toBe(number_format($newData['insurance_cost'], 2, '.', ''))
        ->maintenance_cost->toBe(number_format($newData['maintenance_cost'], 2, '.', ''));
});

test('can validate trip update', function () {
    $trip = Trip::factory()->create();

    Livewire::test(EditTrip::class, ['record' => $trip->getRouteKey()])
        ->fillForm([
            'company_id' => null,
            'driver_id' => null,
            'vehicle_id' => null,
            'start_time' => '',
            'end_time' => '',
            'start_location' => '',
            'end_location' => '',
        ])
        ->call('save')
        ->assertHasFormErrors([
            'company_id' => 'required',
            'driver_id' => 'required',
            'vehicle_id' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
            'start_location' => 'required',
            'end_location' => 'required',
        ]);
});

test('can delete trip', function () {
    $trip = Trip::factory()->create();

    Livewire::test(ListTrips::class)
        ->callTableAction('delete', $trip);

    $this->assertModelMissing($trip);
});

test('can bulk delete trips', function () {
    $trips = Trip::factory()->count(3)->create();

    Livewire::test(ListTrips::class)
        ->callTableBulkAction('delete', $trips);

    foreach ($trips as $trip) {
        $this->assertModelMissing($trip);
    }
});

test('can search trips', function () {
    $company = Company::factory()->create(['name' => 'Searchable Company']);
    $trips = Trip::factory()->count(3)->create();
    $searchableTrip = Trip::factory()->create(['company_id' => $company->id]);

    Livewire::test(ListTrips::class)
        ->searchTable('Searchable Company')
        ->assertCanSeeTableRecords([$searchableTrip])
        ->assertCanNotSeeTableRecords($trips);
});

test('can filter trips by company', function () {
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();
    $trip1 = Trip::factory()->create(['company_id' => $company1->id]);
    $trip2 = Trip::factory()->create(['company_id' => $company2->id]);

    Livewire::test(ListTrips::class)
        ->filterTable('company', $company1->id)
        ->assertCanSeeTableRecords([$trip1])
        ->assertCanNotSeeTableRecords([$trip2]);
});

test('can filter trips by status', function () {
    $pendingTrip = Trip::factory()->create(['status' => 'pending']);
    $completedTrip = Trip::factory()->create(['status' => 'completed']);

    Livewire::test(ListTrips::class)
        ->filterTable('status', 'pending')
        ->assertCanSeeTableRecords([$pendingTrip])
        ->assertCanNotSeeTableRecords([$completedTrip]);
});

test('can filter active trips', function () {
    $activeTrip = Trip::factory()->create([
        'status' => 'in_progress',
        'start_time' => now()->subHour(),
        'end_time' => now()->addHour(),
    ]);
    $inactiveTrip = Trip::factory()->create([
        'status' => 'completed',
        'start_time' => now()->subDays(2),
        'end_time' => now()->subDay(),
    ]);

    Livewire::test(ListTrips::class)
        ->filterTable('active_now', true)
        ->assertCanSeeTableRecords([$activeTrip])
        ->assertCanNotSeeTableRecords([$inactiveTrip]);
});

test('can test fuel cost calculation', function () {
    $company = Company::factory()->create();
    $driver = Driver::factory()->create(['company_id' => $company->id]);
    $vehicle = Vehicle::factory()->create(['company_id' => $company->id]);

    Livewire::test(CreateTrip::class)
        ->fillForm([
            'company_id' => $company->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'fuel_consumption' => 10.0,
            'fuel_price' => 1.50,
        ])
        ->assertFormSet([
            'fuel_cost' => 15.0
        ]);
});

test('can test total cost calculation', function () {
    $company = Company::factory()->create();
    $driver = Driver::factory()->create(['company_id' => $company->id]);
    $vehicle = Vehicle::factory()->create(['company_id' => $company->id]);

    Livewire::test(CreateTrip::class)
        ->fillForm([
            'company_id' => $company->id,
            'driver_id' => $driver->id,
            'vehicle_id' => $vehicle->id,
            'fuel_cost' => 15.0,
            'insurance_cost' => 25.0,
            'maintenance_cost' => 10.0,
        ])
        ->assertFormSet([
            'total_cost' => 50.0
        ]);
});
