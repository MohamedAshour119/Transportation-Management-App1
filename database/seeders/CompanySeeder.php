<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $companies = [
            // Major Transportation Companies
            [
                'name' => 'Swift Transport Solutions',
                'email' => 'info@swifttransport.com',
                'website' => 'https://swifttransport.com',
                'phone' => '+1-555-0101',
                'address' => '1234 Industrial Blvd, Phoenix, AZ 85001'
            ],
            [
                'name' => 'Metro Logistics Corp',
                'email' => 'contact@metrologistics.com',
                'website' => 'https://metrologistics.com',
                'phone' => '+1-555-0202',
                'address' => '5678 Commerce St, Dallas, TX 75201'
            ],
            [
                'name' => 'Eagle Express Freight',
                'email' => 'operations@eagleexpress.com',
                'website' => 'https://eagleexpress.com',
                'phone' => '+1-555-0303',
                'address' => '9012 Highway 101, Los Angeles, CA 90001'
            ],
            [
                'name' => 'Prime Delivery Services',
                'email' => 'dispatch@primedelivery.com',
                'website' => 'https://primedelivery.com',
                'phone' => '+1-555-0404',
                'address' => '3456 Freight Ave, Atlanta, GA 30301'
            ],
            [
                'name' => 'Coastal Cargo Lines',
                'email' => 'admin@coastalcargo.com',
                'website' => 'https://coastalcargo.com',
                'phone' => '+1-555-0505',
                'address' => '7890 Port Rd, Miami, FL 33101'
            ],
            
            // Regional Companies
            [
                'name' => 'Mountain View Transport',
                'email' => 'info@mountainviewtransport.com',
                'website' => 'https://mountainviewtransport.com',
                'phone' => '+1-555-0601',
                'address' => '2345 Alpine Way, Denver, CO 80201'
            ],
            [
                'name' => 'Pacific Northwest Freight',
                'email' => 'dispatch@pnwfreight.com',
                'website' => 'https://pnwfreight.com',
                'phone' => '+1-555-0702',
                'address' => '4567 Cascade Ave, Seattle, WA 98101'
            ],
            [
                'name' => 'Great Lakes Shipping',
                'email' => 'operations@greatlakesship.com',
                'website' => 'https://greatlakesship.com',
                'phone' => '+1-555-0803',
                'address' => '6789 Harbor Dr, Chicago, IL 60601'
            ],
            [
                'name' => 'Desert Star Logistics',
                'email' => 'contact@desertstarlogistics.com',
                'website' => 'https://desertstarlogistics.com',
                'phone' => '+1-555-0904',
                'address' => '8901 Cactus Rd, Las Vegas, NV 89101'
            ],
            [
                'name' => 'Atlantic Express Lines',
                'email' => 'info@atlanticexpress.com',
                'website' => 'https://atlanticexpress.com',
                'phone' => '+1-555-1005',
                'address' => '1357 Ocean Blvd, Virginia Beach, VA 23451'
            ],
            
            // Specialized Companies
            [
                'name' => 'Lone Star Heavy Haul',
                'email' => 'dispatch@lonestarheavy.com',
                'website' => 'https://lonestarheavy.com',
                'phone' => '+1-555-1106',
                'address' => '2468 Ranch Rd, Houston, TX 77001'
            ],
            [
                'name' => 'Northeast Corridor Express',
                'email' => 'operations@necexpress.com',
                'website' => 'https://necexpress.com',
                'phone' => '+1-555-1207',
                'address' => '3579 Liberty St, New York, NY 10001'
            ],
            [
                'name' => 'Golden Gate Transport',
                'email' => 'info@goldengatetrucks.com',
                'website' => 'https://goldengatetrucks.com',
                'phone' => '+1-555-1308',
                'address' => '4680 Bridge Ave, San Francisco, CA 94101'
            ],
            [
                'name' => 'Midwest Cargo Solutions',
                'email' => 'dispatch@midwestcargo.com',
                'website' => 'https://midwestcargo.com',
                'phone' => '+1-555-1409',
                'address' => '5791 Plains Dr, Kansas City, MO 64101'
            ],
            [
                'name' => 'Sunshine State Delivery',
                'email' => 'operations@sunshinedelivery.com',
                'website' => 'https://sunshinedelivery.com',
                'phone' => '+1-555-1510',
                'address' => '6802 Palmetto Way, Orlando, FL 32801'
            ],
            
            // Local/Regional Specialists
            [
                'name' => 'Blue Ridge Freight Co',
                'email' => 'info@blueridgefreight.com',
                'website' => 'https://blueridgefreight.com',
                'phone' => '+1-555-1611',
                'address' => '7913 Mountain View Rd, Asheville, NC 28801'
            ],
            [
                'name' => 'Cascade Logistics Group',
                'email' => 'dispatch@cascadelogistics.com',
                'website' => 'https://cascadelogistics.com',
                'phone' => '+1-555-1712',
                'address' => '8024 Forest Ave, Portland, OR 97201'
            ],
            [
                'name' => 'Heartland Transport Services',
                'email' => 'operations@heartlandtransport.com',
                'website' => 'https://heartlandtransport.com',
                'phone' => '+1-555-1813',
                'address' => '9135 Cornfield Rd, Des Moines, IA 50301'
            ],
            [
                'name' => 'Rocky Mountain Haulers',
                'email' => 'info@rockymountainhaulers.com',
                'website' => 'https://rockymountainhaulers.com',
                'phone' => '+1-555-1914',
                'address' => '1246 Summit Dr, Salt Lake City, UT 84101'
            ],
            [
                'name' => 'Bayou Express Transport',
                'email' => 'dispatch@bayouexpress.com',
                'website' => 'https://bayouexpress.com',
                'phone' => '+1-555-2015',
                'address' => '2357 Cypress St, New Orleans, LA 70112'
            ]
        ];

        foreach ($companies as $company) {
            Company::create($company);
        }
    }
}
