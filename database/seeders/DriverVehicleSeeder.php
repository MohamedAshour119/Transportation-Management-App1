<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DriverVehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $drivers = Driver::with('company')->get();
        $vehicles = Vehicle::with('company')->get();
        
        // Create realistic driver-vehicle assignments
        // Each driver can operate multiple vehicles within their company and sometimes cross-company
        // Heavy truck drivers can drive heavy trucks, medium truck drivers can drive medium and light vehicles
        
        $assignments = [];
        
        // Define vehicle categories by capacity for realistic assignments
        $heavyTrucks = $vehicles->filter(fn($v) => $v->capacity >= 40000); // 40,000+ lbs
        $mediumTrucks = $vehicles->filter(fn($v) => $v->capacity >= 15000 && $v->capacity < 40000); // 15,000-39,999 lbs
        $lightVehicles = $vehicles->filter(fn($v) => $v->capacity < 15000); // Under 15,000 lbs
        
        foreach ($drivers as $driver) {
            $companyVehicles = $vehicles->where('company_id', $driver->company_id);
            $assignedCount = 0;
            $maxAssignments = rand(2, 4); // Each driver can operate 2-4 vehicles
            
            // Assign vehicles based on realistic patterns
            foreach ($companyVehicles as $vehicle) {
                if ($assignedCount >= $maxAssignments) break;
                
                // Probability of assignment based on vehicle type and driver experience
                $shouldAssign = false;
                
                if ($vehicle->capacity >= 40000) {
                    // Heavy trucks - 70% chance for experienced drivers
                    $shouldAssign = rand(1, 100) <= 70;
                } elseif ($vehicle->capacity >= 15000) {
                    // Medium trucks - 80% chance
                    $shouldAssign = rand(1, 100) <= 80;
                } else {
                    // Light vehicles - 90% chance (most drivers can handle these)
                    $shouldAssign = rand(1, 100) <= 90;
                }
                
                if ($shouldAssign) {
                    $assignments[] = [$driver->id, $vehicle->id];
                    $assignedCount++;
                }
            }
            
            // Ensure each driver has at least one vehicle assignment
            if ($assignedCount === 0 && $companyVehicles->count() > 0) {
                $randomVehicle = $companyVehicles->random();
                $assignments[] = [$driver->id, $randomVehicle->id];
            }
            
            // Some experienced drivers can also operate vehicles from partner companies (10% chance)
            if (rand(1, 100) <= 10) {
                $otherCompanyVehicles = $vehicles->where('company_id', '!=', $driver->company_id);
                if ($otherCompanyVehicles->count() > 0) {
                    $partnerVehicle = $otherCompanyVehicles->random();
                    $assignments[] = [$driver->id, $partnerVehicle->id];
                }
            }
        }
        
        // Remove duplicates
        $assignments = array_unique($assignments, SORT_REGULAR);
        
        // Insert assignments
        foreach ($assignments as $assignment) {
            // Check if assignment already exists to avoid duplicates
            $exists = DB::table('driver_vehicle')
                ->where('driver_id', $assignment[0])
                ->where('vehicle_id', $assignment[1])
                ->exists();
                
            if (!$exists) {
                DB::table('driver_vehicle')->insert([
                    'driver_id' => $assignment[0],
                    'vehicle_id' => $assignment[1],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
