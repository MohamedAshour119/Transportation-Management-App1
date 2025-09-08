<?php

namespace Database\Seeders;

use App\Models\Driver;
use App\Models\Company;
use Illuminate\Database\Seeder;

class DriverSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = Company::all();
        
        // Generate drivers for all companies (3-4 drivers per company)
        $drivers = [];
        $firstNames = ['Michael', 'Sarah', 'David', 'Jennifer', 'Robert', 'Lisa', 'James', 'Maria', 'Christopher', 'Amanda', 'Kevin', 'Nicole', 'Daniel', 'Jessica', 'Matthew', 'Ashley', 'Anthony', 'Emily', 'Joshua', 'Samantha', 'Andrew', 'Brittany', 'Kenneth', 'Megan', 'Paul', 'Rachel', 'Steven', 'Lauren', 'Timothy', 'Stephanie', 'Jason', 'Heather', 'Richard', 'Nicole', 'Charles', 'Amy', 'Thomas', 'Angela', 'Christopher', 'Brenda', 'Daniel', 'Emma', 'Matthew', 'Olivia', 'Anthony', 'Cynthia', 'Mark', 'Marie', 'Donald', 'Janet', 'Steven', 'Catherine', 'Paul', 'Frances', 'Andrew', 'Christine', 'Joshua', 'Samantha', 'Kenneth', 'Deborah', 'Kevin', 'Rachel', 'Brian', 'Carolyn', 'George', 'Janet', 'Edward', 'Virginia', 'Ronald', 'Maria', 'Timothy', 'Heather', 'Jason', 'Diane', 'Jeffrey', 'Julie', 'Ryan', 'Joyce', 'Jacob', 'Victoria'];
        $lastNames = ['Johnson', 'Williams', 'Rodriguez', 'Davis', 'Thompson', 'Anderson', 'Wilson', 'Garcia', 'Brown', 'Martinez', 'Taylor', 'White', 'Jackson', 'Harris', 'Martin', 'Clark', 'Lewis', 'Lee', 'Walker', 'Hall', 'Allen', 'Young', 'King', 'Wright', 'Lopez', 'Hill', 'Scott', 'Green', 'Adams', 'Baker', 'Gonzalez', 'Nelson', 'Carter', 'Mitchell', 'Perez', 'Roberts', 'Turner', 'Phillips', 'Campbell', 'Parker', 'Evans', 'Edwards', 'Collins', 'Stewart', 'Sanchez', 'Morris', 'Rogers', 'Reed', 'Cook', 'Morgan', 'Bell', 'Murphy', 'Bailey', 'Rivera', 'Cooper', 'Richardson', 'Cox', 'Howard', 'Ward', 'Torres', 'Peterson', 'Gray', 'Ramirez', 'James', 'Watson', 'Brooks', 'Kelly', 'Sanders', 'Price', 'Bennett', 'Wood', 'Barnes', 'Ross', 'Henderson', 'Coleman', 'Jenkins', 'Perry', 'Powell', 'Long', 'Patterson'];
        
        $stateAbbrevs = ['AZ', 'TX', 'CA', 'GA', 'FL', 'CO', 'WA', 'IL', 'NV', 'VA', 'NY', 'MO', 'NC', 'OR', 'IA', 'UT', 'LA'];
        $phoneCounter = 1000;
        $licenseCounter = 1000;
        
        foreach ($companies as $index => $company) {
            $driversPerCompany = rand(3, 5); // 3-5 drivers per company
            $stateAbbrev = $stateAbbrevs[$index % count($stateAbbrevs)];
            
            for ($i = 0; $i < $driversPerCompany; $i++) {
                $firstName = $firstNames[array_rand($firstNames)];
                $lastName = $lastNames[array_rand($lastNames)];
                $fullName = $firstName . ' ' . $lastName;
                $emailName = strtolower($firstName[0] . $lastName);
                $companyDomain = explode('@', $company['email'])[1];
                
                $drivers[] = [
                    'full_name' => $fullName,
                    'company_id' => $company['id'],
                    'email' => $emailName . '@' . $companyDomain,
                    'phone' => '+1-555-' . str_pad($phoneCounter++, 4, '0', STR_PAD_LEFT),
                    'address' => rand(100, 9999) . ' ' . $lastNames[array_rand($lastNames)] . ' St, City, ' . $stateAbbrev . ' ' . rand(10000, 99999),
                    'license_number' => 'CDL-' . $stateAbbrev . '-' . str_pad($licenseCounter++, 6, '0', STR_PAD_LEFT),
                    'license_expiry_date' => date('Y-m-d', strtotime('+' . rand(6, 24) . ' months'))
                ];
            }
        }

        foreach ($drivers as $driver) {
            Driver::create($driver);
        }
    }
}
