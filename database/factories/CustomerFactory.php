<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Customer>
 */
class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'branch_id' => Branch::factory(),
            'full_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->optional()->safeEmail(),
            'id_number' => fake()->optional()->numerify('########'),
            'address' => fake()->optional()->address(),
            'vehicle_count' => 0,
            'total_visits' => 0,
            'lifetime_spending' => 0,
            'loyalty_points' => 0,
        ];
    }
}
