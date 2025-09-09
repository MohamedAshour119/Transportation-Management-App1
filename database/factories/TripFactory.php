<?php

namespace Database\Factories;

use App\Models\Trip;
use App\Models\Company;
use App\Models\Driver;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Trip>
 */
class TripFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = fake()->dateTimeBetween('-1 month', '+1 month');
        $endTime = fake()->dateTimeBetween($startTime, $startTime->format('Y-m-d H:i:s') . ' +8 hours');
        $distance = fake()->randomFloat(2, 10, 500);
        $fuelConsumption = fake()->randomFloat(2, 5, 50);
        $fuelPrice = fake()->randomFloat(2, 1.2, 2.5);
        $fuelCost = $fuelConsumption * $fuelPrice;
        $insuranceCost = fake()->randomFloat(2, 50, 200);
        $maintenanceCost = fake()->randomFloat(2, 20, 150);
        $totalCost = $fuelCost + $insuranceCost + $maintenanceCost;

        return [
            'company_id' => Company::factory(),
            'driver_id' => Driver::factory(),
            'vehicle_id' => Vehicle::factory(),
            'status' => fake()->randomElement(['scheduled', 'in_progress', 'completed', 'cancelled']),
            'start_time' => $startTime,
            'end_time' => $endTime,
            'start_location' => fake()->address(),
            'end_location' => fake()->address(),
            'distance' => $distance,
            'fuel_consumption' => $fuelConsumption,
            'fuel_price' => $fuelPrice,
            'fuel_cost' => $fuelCost,
            'insurance_cost' => $insuranceCost,
            'maintenance_cost' => $maintenanceCost,
            'total_cost' => $totalCost,
        ];
    }

    /**
     * Indicate that the trip is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'scheduled',
        ]);
    }

    /**
     * Indicate that the trip is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'in_progress',
        ]);
    }

    /**
     * Indicate that the trip is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    /**
     * Indicate that the trip is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelled',
        ]);
    }
}
