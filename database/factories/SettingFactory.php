<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Setting>
 */
class SettingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'branch_id' => null,
            'group' => fake()->randomElement(['company', 'receipt', 'sms', 'email']),
            'key' => fake()->unique()->word(),
            'value' => fake()->sentence(),
            'type' => 'string',
        ];
    }

    public function forBranch(Branch $branch): static
    {
        return $this->state(fn () => ['branch_id' => $branch->id]);
    }
}
