<?php

namespace Database\Seeders;

use App\Models\Trip;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class TripSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();
        $drivers = Driver::all();
        $vehicles = Vehicle::all();
        
        // Generate many more trips dynamically
        $trips = [];
        $locations = [
            'Phoenix, AZ', 'Dallas, TX', 'Los Angeles, CA', 'Atlanta, GA', 'Miami, FL',
            'Denver, CO', 'Seattle, WA', 'Chicago, IL', 'Las Vegas, NV', 'Virginia Beach, VA',
            'New York, NY', 'Kansas City, MO', 'Asheville, NC', 'Portland, OR', 'Des Moines, IA',
            'Salt Lake City, UT', 'New Orleans, LA', 'Houston, TX', 'San Francisco, CA',
            'Tampa, FL', 'Orlando, FL', 'Jacksonville, FL', 'Savannah, GA', 'Charlotte, NC',
            'Memphis, TN', 'Nashville, TN', 'Louisville, KY', 'Indianapolis, IN', 'Columbus, OH',
            'Detroit, MI', 'Milwaukee, WI', 'Minneapolis, MN', 'Omaha, NE', 'Oklahoma City, OK'
        ];
        
        $statuses = ['pending', 'scheduled', 'in_progress', 'completed', 'cancelled'];
        $statusWeights = [15, 25, 8, 45, 7]; // Weighted distribution
        
        // Create active trips (in progress right now)
        for ($i = 0; $i < 12; $i++) {
            $driver = $drivers->random();
            $vehicle = $vehicles->where('company_id', $driver->company_id)->random();
            $startLocation = $locations[array_rand($locations)];
            $endLocation = $locations[array_rand($locations)];
            $distance = rand(150, 1200) + (rand(0, 99) / 100);
            
            $trips[] = [
                'company_id' => $driver->company_id,
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'status' => 'in_progress',
                'start_time' => Carbon::now()->subHours(rand(1, 8)),
                'end_time' => Carbon::now()->addHours(rand(2, 12)),
                'start_location' => $startLocation,
                'end_location' => $endLocation,
                'distance' => $distance,
                'fuel_consumption' => $distance / rand(6, 9),
                'fuel_price' => rand(350, 450) / 100,
                'fuel_cost' => ($distance / rand(6, 9)) * (rand(350, 450) / 100),
                'insurance_cost' => rand(25, 85),
                'maintenance_cost' => rand(15, 45),
                'total_cost' => (($distance / rand(6, 9)) * (rand(350, 450) / 100)) + rand(25, 85) + rand(15, 45)
            ];
        }
        
        // Create completed trips this month
        for ($i = 0; $i < 35; $i++) {
            $driver = $drivers->random();
            $vehicle = $vehicles->where('company_id', $driver->company_id)->random();
            $startLocation = $locations[array_rand($locations)];
            $endLocation = $locations[array_rand($locations)];
            $distance = rand(100, 1500) + (rand(0, 99) / 100);
            $daysAgo = rand(1, 30);
            
            $trips[] = [
                'company_id' => $driver->company_id,
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'status' => 'completed',
                'start_time' => Carbon::now()->subDays($daysAgo)->setHour(rand(5, 10)),
                'end_time' => Carbon::now()->subDays($daysAgo)->setHour(rand(14, 22)),
                'start_location' => $startLocation,
                'end_location' => $endLocation,
                'distance' => $distance,
                'fuel_consumption' => $distance / rand(6, 9),
                'fuel_price' => rand(350, 450) / 100,
                'fuel_cost' => ($distance / rand(6, 9)) * (rand(350, 450) / 100),
                'insurance_cost' => rand(25, 85),
                'maintenance_cost' => rand(15, 45),
                'total_cost' => (($distance / rand(6, 9)) * (rand(350, 450) / 100)) + rand(25, 85) + rand(15, 45)
            ];
        }
        
        // Create scheduled trips (future)
        for ($i = 0; $i < 25; $i++) {
            $driver = $drivers->random();
            $vehicle = $vehicles->where('company_id', $driver->company_id)->random();
            $startLocation = $locations[array_rand($locations)];
            $endLocation = $locations[array_rand($locations)];
            $distance = rand(200, 1000) + (rand(0, 99) / 100);
            $daysFromNow = rand(1, 14);
            
            $trips[] = [
                'company_id' => $driver->company_id,
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'status' => 'scheduled',
                'start_time' => Carbon::now()->addDays($daysFromNow)->setHour(rand(6, 10)),
                'end_time' => Carbon::now()->addDays($daysFromNow)->setHour(rand(16, 22)),
                'start_location' => $startLocation,
                'end_location' => $endLocation,
                'distance' => $distance,
                'fuel_consumption' => $distance / rand(6, 9),
                'fuel_price' => rand(350, 450) / 100,
                'fuel_cost' => ($distance / rand(6, 9)) * (rand(350, 450) / 100),
                'insurance_cost' => rand(25, 85),
                'maintenance_cost' => rand(15, 45),
                'total_cost' => (($distance / rand(6, 9)) * (rand(350, 450) / 100)) + rand(25, 85) + rand(15, 45)
            ];
        }
        
        // Create pending trips
        for ($i = 0; $i < 18; $i++) {
            $driver = $drivers->random();
            $vehicle = $vehicles->where('company_id', $driver->company_id)->random();
            $startLocation = $locations[array_rand($locations)];
            $endLocation = $locations[array_rand($locations)];
            $distance = rand(150, 800) + (rand(0, 99) / 100);
            $daysFromNow = rand(3, 21);
            
            $trips[] = [
                'company_id' => $driver->company_id,
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'status' => 'pending',
                'start_time' => Carbon::now()->addDays($daysFromNow)->setHour(rand(6, 10)),
                'end_time' => Carbon::now()->addDays($daysFromNow)->setHour(rand(16, 22)),
                'start_location' => $startLocation,
                'end_location' => $endLocation,
                'distance' => $distance,
                'fuel_consumption' => $distance / rand(6, 9),
                'fuel_price' => rand(350, 450) / 100,
                'fuel_cost' => ($distance / rand(6, 9)) * (rand(350, 450) / 100),
                'insurance_cost' => rand(25, 85),
                'maintenance_cost' => rand(15, 45),
                'total_cost' => (($distance / rand(6, 9)) * (rand(350, 450) / 100)) + rand(25, 85) + rand(15, 45)
            ];
        }
        
        // Add some historical trips from previous months for variety
        for ($i = 0; $i < 40; $i++) {
            $driver = $drivers->random();
            $vehicle = $vehicles->where('company_id', $driver->company_id)->random();
            $startLocation = $locations[array_rand($locations)];
            $endLocation = $locations[array_rand($locations)];
            $distance = rand(100, 1200) + (rand(0, 99) / 100);
            $monthsAgo = rand(1, 6);
            $daysAgo = rand(30 * $monthsAgo, 30 * ($monthsAgo + 1));
            
            $trips[] = [
                'company_id' => $driver->company_id,
                'driver_id' => $driver->id,
                'vehicle_id' => $vehicle->id,
                'status' => 'completed',
                'start_time' => Carbon::now()->subDays($daysAgo)->setHour(rand(5, 10)),
                'end_time' => Carbon::now()->subDays($daysAgo)->setHour(rand(14, 22)),
                'start_location' => $startLocation,
                'end_location' => $endLocation,
                'distance' => $distance,
                'fuel_consumption' => $distance / rand(6, 9),
                'fuel_price' => rand(350, 450) / 100,
                'fuel_cost' => ($distance / rand(6, 9)) * (rand(350, 450) / 100),
                'insurance_cost' => rand(25, 85),
                'maintenance_cost' => rand(15, 45),
                'total_cost' => (($distance / rand(6, 9)) * (rand(350, 450) / 100)) + rand(25, 85) + rand(15, 45)
            ];
        }

        foreach ($trips as $trip) {
            Trip::create($trip);
        }
    }
}
