<?php

namespace Database\Factories;

use App\Models\Vehicle;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vehicle>
 */
class VehicleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $makes = ['Toyota', 'Ford', 'Mercedes', 'Volvo', 'Scania', 'MAN', 'Iveco'];
        $make = fake()->randomElement($makes);
        
        return [
            'make' => $make,
            'model' => fake()->word() . ' ' . fake()->numberBetween(100, 999),
            'year' => fake()->numberBetween(2015, 2024),
            'company_id' => Company::factory(),
            'capacity' => fake()->numberBetween(5, 50),
            'plate_number' => fake()->regexify('[A-Z]{3}-[0-9]{4}'),
            'vehicle_identification_number' => fake()->regexify('[A-Z0-9]{17}'),
        ];
    }
}
