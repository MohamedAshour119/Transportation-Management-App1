<?php

use App\Models\Vehicle;
use App\Models\Company;
use App\Models\User;
use Livewire\Livewire;
use App\Filament\Resources\VehicleResource;
use App\Filament\Resources\VehicleResource\Pages\CreateVehicle;
use App\Filament\Resources\VehicleResource\Pages\EditVehicle;
use App\Filament\Resources\VehicleResource\Pages\ListVehicles;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('can render vehicle list page', function () {
    $this->get(VehicleResource::getUrl('index'))->assertSuccessful();
});

test('can render vehicle create page', function () {
    $this->get(VehicleResource::getUrl('create'))->assertSuccessful();
});

test('can create vehicle', function () {
    $company = Company::factory()->create();
    $newData = Vehicle::factory()->make(['company_id' => $company->id]);

    Livewire::test(CreateVehicle::class)
        ->fillForm([
            'make' => $newData->make,
            'model' => $newData->model,
            'year' => $newData->year,
            'company_id' => $newData->company_id,
            'capacity' => $newData->capacity,
            'plate_number' => $newData->plate_number,
            'vehicle_identification_number' => $newData->vehicle_identification_number,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Vehicle::class, [
        'make' => $newData->make,
        'model' => $newData->model,
        'year' => $newData->year,
        'company_id' => $newData->company_id,
        'capacity' => $newData->capacity,
        'plate_number' => $newData->plate_number,
        'vehicle_identification_number' => $newData->vehicle_identification_number,
    ]);
});

test('can validate vehicle creation', function () {
    Livewire::test(CreateVehicle::class)
        ->fillForm([
            'make' => '',
            'model' => '',
            'year' => 1800, // Invalid year
            'company_id' => null,
            'capacity' => 0, // Invalid capacity
        ])
        ->call('create')
        ->assertHasFormErrors([
            'make' => 'required',
            'model' => 'required',
            'year' => 'min',
            'company_id' => 'required',
            'capacity' => 'min',
        ]);
});

test('can validate unique plate number on vehicle creation', function () {
    $existingVehicle = Vehicle::factory()->create();

    Livewire::test(CreateVehicle::class)
        ->fillForm([
            'make' => 'Test Make',
            'model' => 'Test Model',
            'year' => 2020,
            'company_id' => Company::factory()->create()->id,
            'plate_number' => $existingVehicle->plate_number,
        ])
        ->call('create')
        ->assertHasFormErrors(['plate_number' => 'unique']);
});

test('can validate unique VIN on vehicle creation', function () {
    $existingVehicle = Vehicle::factory()->create();

    Livewire::test(CreateVehicle::class)
        ->fillForm([
            'make' => 'Test Make',
            'model' => 'Test Model',
            'year' => 2020,
            'company_id' => Company::factory()->create()->id,
            'vehicle_identification_number' => $existingVehicle->vehicle_identification_number,
        ])
        ->call('create')
        ->assertHasFormErrors(['vehicle_identification_number' => 'unique']);
});

test('can render vehicle edit page', function () {
    $vehicle = Vehicle::factory()->create();

    $this->get(VehicleResource::getUrl('edit', ['record' => $vehicle]))->assertSuccessful();
});

test('can retrieve vehicle data for editing', function () {
    $vehicle = Vehicle::factory()->create();

    Livewire::test(EditVehicle::class, ['record' => $vehicle->getRouteKey()])
        ->assertFormSet([
            'make' => $vehicle->make,
            'model' => $vehicle->model,
            'year' => $vehicle->year,
            'company_id' => $vehicle->company_id,
            'capacity' => $vehicle->capacity,
            'plate_number' => $vehicle->plate_number,
            'vehicle_identification_number' => $vehicle->vehicle_identification_number,
        ]);
});

test('can update vehicle', function () {
    $vehicle = Vehicle::factory()->create();
    $newData = Vehicle::factory()->make(['company_id' => $vehicle->company_id]);

    Livewire::test(EditVehicle::class, ['record' => $vehicle->getRouteKey()])
        ->fillForm([
            'make' => $newData->make,
            'model' => $newData->model,
            'year' => $newData->year,
            'company_id' => $newData->company_id,
            'capacity' => $newData->capacity,
            'plate_number' => $newData->plate_number,
            'vehicle_identification_number' => $newData->vehicle_identification_number,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($vehicle->refresh())
        ->make->toBe($newData->make)
        ->model->toBe($newData->model)
        ->year->toBe($newData->year)
        ->company_id->toBe($newData->company_id)
        ->capacity->toBe($newData->capacity)
        ->plate_number->toBe($newData->plate_number)
        ->vehicle_identification_number->toBe($newData->vehicle_identification_number);
});

test('can validate vehicle update', function () {
    $vehicle = Vehicle::factory()->create();

    Livewire::test(EditVehicle::class, ['record' => $vehicle->getRouteKey()])
        ->fillForm([
            'make' => '',
            'model' => '',
            'year' => 1800,
            'company_id' => null,
            'capacity' => 0,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'make' => 'required',
            'model' => 'required',
            'year' => 'min',
            'company_id' => 'required',
            'capacity' => 'min',
        ]);
});

test('can delete vehicle', function () {
    $vehicle = Vehicle::factory()->create();

    Livewire::test(ListVehicles::class)
        ->callTableAction('delete', $vehicle);

    $this->assertModelMissing($vehicle);
});

test('can bulk delete vehicles', function () {
    $vehicles = Vehicle::factory()->count(3)->create();

    Livewire::test(ListVehicles::class)
        ->callTableBulkAction('delete', $vehicles);

    foreach ($vehicles as $vehicle) {
        $this->assertModelMissing($vehicle);
    }
});


test('can filter vehicles by company', function () {
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();
    $vehicle1 = Vehicle::factory()->create(['company_id' => $company1->id]);
    $vehicle2 = Vehicle::factory()->create(['company_id' => $company2->id]);

    Livewire::test(ListVehicles::class)
        ->filterTable('company', $company1->id)
        ->assertCanSeeTableRecords([$vehicle1])
        ->assertCanNotSeeTableRecords([$vehicle2]);
});

test('can filter vehicles by make', function () {
    $vehicle1 = Vehicle::factory()->create(['make' => 'Toyota']);
    $vehicle2 = Vehicle::factory()->create(['make' => 'Honda']);

    Livewire::test(ListVehicles::class)
        ->filterTable('make', 'Toyota')
        ->assertCanSeeTableRecords([$vehicle1])
        ->assertCanNotSeeTableRecords([$vehicle2]);
});

test('can filter high capacity vehicles', function () {
    $highCapacityVehicle = Vehicle::factory()->create(['capacity' => 25]);
    $lowCapacityVehicle = Vehicle::factory()->create(['capacity' => 5]);

    Livewire::test(ListVehicles::class)
        ->filterTable('high_capacity', true)
        ->assertCanSeeTableRecords([$highCapacityVehicle])
        ->assertCanNotSeeTableRecords([$lowCapacityVehicle]);
});

test('can filter vehicles with trips', function () {
    $vehicleWithTrips = Vehicle::factory()->hasTrips(2)->create();
    $vehicleWithoutTrips = Vehicle::factory()->create();

    Livewire::test(ListVehicles::class)
        ->filterTable('has_trips', true)
        ->assertCanSeeTableRecords([$vehicleWithTrips])
        ->assertCanNotSeeTableRecords([$vehicleWithoutTrips]);
});
