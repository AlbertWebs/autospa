<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Branch>
 */
class BranchFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->company().' Branch';

        return [
            'uuid' => (string) Str::uuid(),
            'name' => $name,
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->companyEmail(),
            'is_active' => true,
            'settings' => [],
        ];
    }
}
