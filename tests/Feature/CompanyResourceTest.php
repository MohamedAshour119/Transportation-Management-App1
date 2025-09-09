<?php

use App\Models\Company;
use App\Models\User;
use Livewire\Livewire;
use App\Filament\Resources\CompanyResource;
use App\Filament\Resources\CompanyResource\Pages\CreateCompany;
use App\Filament\Resources\CompanyResource\Pages\EditCompany;
use App\Filament\Resources\CompanyResource\Pages\ListCompanies;
use Filament\Actions\DeleteAction;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});



test('can render company list page', function () {
    $this->get(CompanyResource::getUrl('index'))->assertSuccessful();
});

test('can render company create page', function () {
    $this->get(CompanyResource::getUrl('create'))->assertSuccessful();
});

test('can create company', function () {
    $newData = Company::factory()->make();

    Livewire::test(CreateCompany::class)
        ->fillForm([
            'name' => $newData->name,
            'email' => $newData->email,
            'website' => $newData->website,
            'phone' => $newData->phone,
            'address' => $newData->address,
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(Company::class, [
        'name' => $newData->name,
        'email' => $newData->email,
        'website' => $newData->website,
        'phone' => $newData->phone,
        'address' => $newData->address,
    ]);
});

test('can validate company creation', function () {
    Livewire::test(CreateCompany::class)
        ->fillForm([
            'name' => '',
            'email' => 'invalid-email',
        ])
        ->call('create')
        ->assertHasFormErrors([
            'name' => 'required',
            'email' => 'email',
        ]);
});

test('can validate unique email on company creation', function () {
    $existingCompany = Company::factory()->create();

    Livewire::test(CreateCompany::class)
        ->fillForm([
            'name' => 'Test Company',
            'email' => $existingCompany->email,
        ])
        ->call('create')
        ->assertHasFormErrors(['email' => 'unique']);
});

test('can render company edit page', function () {
    $company = Company::factory()->create();

    $this->get(CompanyResource::getUrl('edit', ['record' => $company]))->assertSuccessful();
});

test('can retrieve company data for editing', function () {
    $company = Company::factory()->create();

    Livewire::test(EditCompany::class, ['record' => $company->getRouteKey()])
        ->assertFormSet([
            'name' => $company->name,
            'email' => $company->email,
            'website' => $company->website,
            'phone' => $company->phone,
            'address' => $company->address,
        ]);
});

test('can update company', function () {
    $company = Company::factory()->create();
    $newData = Company::factory()->make();

    Livewire::test(EditCompany::class, ['record' => $company->getRouteKey()])
        ->fillForm([
            'name' => $newData->name,
            'email' => $newData->email,
            'website' => $newData->website,
            'phone' => $newData->phone,
            'address' => $newData->address,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($company->refresh())
        ->name->toBe($newData->name)
        ->email->toBe($newData->email)
        ->website->toBe($newData->website)
        ->phone->toBe($newData->phone)
        ->address->toBe($newData->address);
});

test('can validate company update', function () {
    $company = Company::factory()->create();

    Livewire::test(EditCompany::class, ['record' => $company->getRouteKey()])
        ->fillForm([
            'name' => '',
            'email' => 'invalid-email',
        ])
        ->call('save')
        ->assertHasFormErrors([
            'name' => 'required',
            'email' => 'email',
        ]);
});

test('can delete company from list page', function () {
    $company = Company::factory()->create();

    Livewire::test(ListCompanies::class)
        ->callTableAction('delete', $company);

    $this->assertModelMissing($company);
});

test('can bulk delete companies', function () {
    $companies = Company::factory()->count(3)->create();

    Livewire::test(ListCompanies::class)
        ->callTableBulkAction('delete', $companies);

    foreach ($companies as $company) {
        $this->assertModelMissing($company);
    }
});

test('can delete company from edit page', function () {
    $company = Company::factory()->create();

    Livewire::test(EditCompany::class, ['record' => $company->getRouteKey()])
        ->callAction(DeleteAction::class);

    $this->assertModelMissing($company);
});

test('can search companies', function () {
    $companies = Company::factory()->count(3)->create();
    $searchableCompany = $companies->first();

    Livewire::test(ListCompanies::class)
        ->searchTable($searchableCompany->name)
        ->assertCanSeeTableRecords([$searchableCompany])
        ->assertCanNotSeeTableRecords($companies->skip(1));
});

test('can filter companies by has drivers', function () {
    $companyWithDrivers = Company::factory()->hasDrivers(2)->create();
    $companyWithoutDrivers = Company::factory()->create();

    Livewire::test(ListCompanies::class)
        ->filterTable('has_drivers', true)
        ->assertCanSeeTableRecords([$companyWithDrivers])
        ->assertCanNotSeeTableRecords([$companyWithoutDrivers]);
});

test('can filter companies by has vehicles', function () {
    $companyWithVehicles = Company::factory()->hasVehicles(2)->create();
    $companyWithoutVehicles = Company::factory()->create();

    Livewire::test(ListCompanies::class)
        ->filterTable('has_vehicles', true)
        ->assertCanSeeTableRecords([$companyWithVehicles])
        ->assertCanNotSeeTableRecords([$companyWithoutVehicles]);
});
