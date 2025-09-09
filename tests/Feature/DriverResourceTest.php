<?php

use App\Models\Driver;
use App\Models\Company;
use App\Models\User;
use Livewire\Livewire;
use App\Filament\Resources\DriverResource;
use App\Filament\Resources\DriverResource\Pages\CreateDriver;
use App\Filament\Resources\DriverResource\Pages\EditDriver;
use App\Filament\Resources\DriverResource\Pages\ListDrivers;
use Filament\Actions\DeleteAction;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('can render driver list page', function () {
    $this->get(DriverResource::getUrl('index'))->assertSuccessful();
});

test('can render driver create page', function () {
    $this->get(DriverResource::getUrl('create'))->assertSuccessful();
});

test('can create driver', function () {
    $company = Company::factory()->create();
    $newData = Driver::factory()->make(['company_id' => $company->id]);

    Livewire::test(CreateDriver::class)
        ->fillForm([
            'full_name' => $newData->full_name,
            'company_id' => $newData->company_id,
            'email' => $newData->email,
            'phone' => $newData->phone,
            'address' => $newData->address,
            'license_number' => $newData->license_number,
            'license_expiry_date' => $newData->license_expiry_date?->format('Y-m-d'),
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Driver::class, [
        'full_name' => $newData->full_name,
        'company_id' => $newData->company_id,
        'email' => $newData->email,
        'phone' => $newData->phone,
        'address' => $newData->address,
        'license_number' => $newData->license_number,
    ]);
});

test('can validate driver creation', function () {
    Livewire::test(CreateDriver::class)
        ->fillForm([
            'full_name' => '',
            'email' => 'invalid-email',
            'company_id' => null,
        ])
        ->call('create')
        ->assertHasFormErrors([
            'full_name' => 'required',
            'email' => 'email',
            'company_id' => 'required',
        ]);
});

test('can validate unique email on driver creation', function () {
    $existingDriver = Driver::factory()->create();

    Livewire::test(CreateDriver::class)
        ->fillForm([
            'full_name' => 'Test Driver',
            'email' => $existingDriver->email,
            'company_id' => Company::factory()->create()->id,
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique']);
});

test('can render driver edit page', function () {
    $driver = Driver::factory()->create();

    $this->get(DriverResource::getUrl('edit', ['record' => $driver]))->assertSuccessful();
});

test('can retrieve driver data for editing', function () {
    $driver = Driver::factory()->create();

    Livewire::test(EditDriver::class, ['record' => $driver->getRouteKey()])
        ->assertFormSet([
            'full_name' => $driver->full_name,
            'company_id' => $driver->company_id,
            'email' => $driver->email,
            'phone' => $driver->phone,
            'address' => $driver->address,
            'license_number' => $driver->license_number,
            'license_expiry_date' => $driver->license_expiry_date->format('Y-m-d H:i:s'),
        ]);
});

test('can update driver', function () {
    $driver = Driver::factory()->create();
    $newData = Driver::factory()->make(['company_id' => $driver->company_id]);

    Livewire::test(EditDriver::class, ['record' => $driver->getRouteKey()])
        ->fillForm([
            'full_name' => $newData->full_name,
            'company_id' => $newData->company_id,
            'email' => $newData->email,
            'phone' => $newData->phone,
            'address' => $newData->address,
            'license_number' => $newData->license_number,
            'license_expiry_date' => $newData->license_expiry_date,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($driver->refresh())
        ->full_name->toBe($newData->full_name)
        ->company_id->toBe($newData->company_id)
        ->email->toBe($newData->email)
        ->phone->toBe($newData->phone)
        ->address->toBe($newData->address)
        ->license_number->toBe($newData->license_number);
});

test('can validate driver update', function () {
    $driver = Driver::factory()->create();

    Livewire::test(EditDriver::class, ['record' => $driver->getRouteKey()])
        ->fillForm([
            'full_name' => '',
            'email' => 'invalid-email',
            'company_id' => null,
        ])
        ->call('save')
        ->assertHasFormErrors([
            'full_name' => 'required',
            'email' => 'email',
            'company_id' => 'required',
        ]);
});

test('can delete driver', function () {
    $driver = Driver::factory()->create();

    Livewire::test(ListDrivers::class)
        ->callTableAction('delete', $driver);

    $this->assertModelMissing($driver);
});

test('can bulk delete drivers', function () {
    $drivers = Driver::factory()->count(3)->create();

    Livewire::test(ListDrivers::class)
        ->callTableBulkAction('delete', $drivers);

    foreach ($drivers as $driver) {
        $this->assertModelMissing($driver);
    }
});

test('can delete driver from edit page', function () {
    $driver = Driver::factory()->create();

    Livewire::test(EditDriver::class, ['record' => $driver->getRouteKey()])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($driver);
});

test('can search drivers', function () {
    $drivers = Driver::factory()->count(3)->create();
    $searchableDriver = $drivers->first();

    Livewire::test(ListDrivers::class)
        ->searchTable($searchableDriver->full_name)
        ->assertCanSeeTableRecords([$searchableDriver])
        ->assertCanNotSeeTableRecords($drivers->skip(1));
});

test('can filter drivers by company', function () {
    $company1 = Company::factory()->create();
    $company2 = Company::factory()->create();
    $driver1 = Driver::factory()->create(['company_id' => $company1->id]);
    $driver2 = Driver::factory()->create(['company_id' => $company2->id]);

    Livewire::test(ListDrivers::class)
        ->filterTable('company', $company1->id)
        ->assertCanSeeTableRecords([$driver1])
        ->assertCanNotSeeTableRecords([$driver2]);
});

test('can filter drivers with expiring licenses', function () {
    $driverExpiringLicense = Driver::factory()->create([
        'license_expiry_date' => now()->addMonth()
    ]);
    $driverValidLicense = Driver::factory()->create([
        'license_expiry_date' => now()->addYear()
    ]);

    Livewire::test(ListDrivers::class)
        ->filterTable('license_expiring_soon', true)
        ->assertCanSeeTableRecords([$driverExpiringLicense])
        ->assertCanNotSeeTableRecords([$driverValidLicense]);
});

test('can filter drivers with trips', function () {
    $driverWithTrips = Driver::factory()->hasTrips(2)->create();
    $driverWithoutTrips = Driver::factory()->create();

    Livewire::test(ListDrivers::class)
        ->filterTable('has_trips', true)
        ->assertCanSeeTableRecords([$driverWithTrips])
        ->assertCanNotSeeTableRecords([$driverWithoutTrips]);
});
