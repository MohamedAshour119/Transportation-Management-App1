<?php

namespace Database\Seeders;

use App\Models\Vehicle;
use App\Models\Company;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();
        
        // Vehicle makes and models with realistic configurations
        $vehicleTypes = [
            'heavy_truck' => [
                ['make' => 'Freightliner', 'model' => 'Cascadia', 'capacity' => 80],
                ['make' => 'Peterbilt', 'model' => '579', 'capacity' => 80],
                ['make' => 'Kenworth', 'model' => 'T680', 'capacity' => 80],
                ['make' => 'Volvo', 'model' => 'VNL 760', 'capacity' => 80],
                ['make' => 'Mack', 'model' => 'Anthem', 'capacity' => 80],
                ['make' => 'International', 'model' => 'LT625', 'capacity' => 80],
                ['make' => 'Peterbilt', 'model' => '389', 'capacity' => 80],
                ['make' => 'Kenworth', 'model' => 'W990', 'capacity' => 80],
            ],
            'medium_truck' => [
                ['make' => 'Freightliner', 'model' => 'M2 106', 'capacity' => 50],
                ['make' => 'International', 'model' => 'MV607', 'capacity' => 50],
                ['make' => 'Isuzu', 'model' => 'NPR-HD', 'capacity' => 50],
                ['make' => 'Hino', 'model' => '268A', 'capacity' => 50],
                ['make' => 'Volvo', 'model' => 'VHD200', 'capacity' => 50],
                ['make' => 'Mack', 'model' => 'MD7', 'capacity' => 50],
            ],
            'light_truck' => [
                ['make' => 'Ford', 'model' => 'E-450', 'capacity' => 30],
                ['make' => 'Chevrolet', 'model' => 'Express 3500', 'capacity' => 30],
                ['make' => 'Ford', 'model' => 'Transit 350', 'capacity' => 30],
                ['make' => 'Mercedes', 'model' => 'Sprinter 3500', 'capacity' => 30],
                ['make' => 'Ram', 'model' => 'ProMaster 3500', 'capacity' => 30],
                ['make' => 'Nissan', 'model' => 'NV200', 'capacity' => 30],
            ]
        ];
        
        $stateAbbrevs = ['AZ', 'TX', 'CA', 'GA', 'FL', 'CO', 'WA', 'IL', 'NV', 'VA', 'NY', 'MO', 'NC', 'OR', 'IA', 'UT', 'LA'];
        $plateCounter = 1;
        $vinCounter = 100000;
        
        $vehicles = [];
        
        foreach ($companies as $index => $company) {
            $vehiclesPerCompany = rand(4, 6); // 4-6 vehicles per company
            $stateAbbrev = $stateAbbrevs[$index % count($stateAbbrevs)];
            $companyAbbrev = strtoupper(substr($company->name, 0, 2));
            
            for ($i = 0; $i < $vehiclesPerCompany; $i++) {
                // Determine vehicle type based on company size and index
                if ($i < 2) {
                    $type = 'heavy_truck'; // First 2 are heavy trucks
                } elseif ($i < 4) {
                    $type = 'medium_truck'; // Next 2 are medium trucks
                } else {
                    $type = 'light_truck'; // Rest are light trucks
                }
                
                $vehicleSpec = $vehicleTypes[$type][array_rand($vehicleTypes[$type])];
                $year = rand(2018, 2024);
                
                $vehicles[] = [
                    'make' => $vehicleSpec['make'],
                    'model' => $vehicleSpec['model'],
                    'year' => $year,
                    'company_id' => $company->id,
                    'capacity' => $vehicleSpec['capacity'],
                    'plate_number' => $stateAbbrev . '-' . $companyAbbrev . '-' . str_pad($plateCounter++, 3, '0', STR_PAD_LEFT),
                    'vehicle_identification_number' => $this->generateVIN($vehicleSpec['make'], $year, $vinCounter++)
                ];
            }
        }

        foreach ($vehicles as $vehicle) {
            Vehicle::create($vehicle);
        }
    }
    
    private function generateVIN($make, $year, $counter)
    {
        $makeCodes = [
            'Freightliner' => '1FUJG',
            'Peterbilt' => '1XP5D',
            'Kenworth' => '1XKWD',
            'Volvo' => '4V4NC',
            'Mack' => '1M1AW',
            'International' => '1HSHB',
            'Isuzu' => 'JALC4',
            'Hino' => '5PVNF',
            'Ford' => '1FDXE',
            'Chevrolet' => '1GC3G',
            'Mercedes' => 'WD3PE',
            'Ram' => '3C6UR',
            'Nissan' => '3N6CM'
        ];
        
        $makeCode = $makeCodes[$make] ?? '1ABCD';
        $yearCode = substr($year, -1);
        $serialNumber = str_pad($counter, 6, '0', STR_PAD_LEFT);
        
        return $makeCode . 'HDV' . $yearCode . 'N' . $serialNumber;
    }
}
