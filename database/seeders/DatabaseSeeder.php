<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        User::factory()->create([
            'name' => 'Transportation Admin',
            'email' => 'admin@transportmanager.com',
        ]);

        // Run seeders in correct order (respecting foreign key constraints)
        $this->call([
            CompanySeeder::class,
            DriverSeeder::class,
            VehicleSeeder::class,
            DriverVehicleSeeder::class,
            TripSeeder::class,
        ]);
    }
}
