<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Driver>
 */
class DriverFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'company_id' => Company::factory(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'license_number' => fake()->regexify('[A-Z]{2}[0-9]{6}'),
            'license_expiry_date' => fake()->dateTimeBetween('now', '+5 years')->format('Y-m-d'),
        ];
    }
}
