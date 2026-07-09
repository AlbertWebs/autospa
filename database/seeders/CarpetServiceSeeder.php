<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class CarpetServiceSeeder extends Seeder
{
    /**
     * @var array<int, array{
     *     name: string,
     *     price: float,
     *     duration_minutes: int,
     *     description: string,
     * }>
     */
    protected array $services = [
        [
            'name' => 'Standard Carpet - Small (Up to 4x6 ft)',
            'price' => 1000,
            'duration_minutes' => 45,
            'description' => 'Standard carpets (Synthetic / Nylon / Polyester). Price range: KSh 800 - 1,200.',
        ],
        [
            'name' => 'Standard Carpet - Medium (5x8 ft to 6x9 ft)',
            'price' => 1750,
            'duration_minutes' => 60,
            'description' => 'Standard carpets (Synthetic / Nylon / Polyester). Price range: KSh 1,500 - 2,000.',
        ],
        [
            'name' => 'Standard Carpet - Large (8x10 ft to 9x12 ft)',
            'price' => 3000,
            'duration_minutes' => 90,
            'description' => 'Standard carpets (Synthetic / Nylon / Polyester). Price range: KSh 2,500 - 3,500.',
        ],
        [
            'name' => 'Standard Carpet - Extra Large (10x14 ft+)',
            'price' => 4000,
            'duration_minutes' => 120,
            'description' => 'Standard carpets (Synthetic / Nylon / Polyester). Fixed price: KSh 4,000.',
        ],
        [
            'name' => 'Premium Carpet - Small (Up to 4x6 ft)',
            'price' => 1750,
            'duration_minutes' => 60,
            'description' => 'Premium carpets (Wool / Shaggy / Persian / Oriental). Price range: KSh 1,500 - 2,000.',
        ],
        [
            'name' => 'Premium Carpet - Medium (5x8 ft to 6x9 ft)',
            'price' => 3000,
            'duration_minutes' => 90,
            'description' => 'Premium carpets (Wool / Shaggy / Persian / Oriental). Price range: KSh 2,500 - 3,500.',
        ],
        [
            'name' => 'Premium Carpet - Large (8x10 ft to 9x12 ft)',
            'price' => 5250,
            'duration_minutes' => 120,
            'description' => 'Premium carpets (Wool / Shaggy / Persian / Oriental). Price range: KSh 4,500 - 6,000.',
        ],
        [
            'name' => 'Premium Carpet - Extra Large (10x14 ft+)',
            'price' => 7000,
            'duration_minutes' => 150,
            'description' => 'Premium carpets (Wool / Shaggy / Persian / Oriental). Fixed price: KSh 7,000.',
        ],
        [
            'name' => 'Commercial & Office Carpet Cleaning (Wall-to-Wall)',
            'price' => 5000,
            'duration_minutes' => 180,
            'description' => 'Commercial and office carpets. Per square foot: KSh 15 - 25. Minimum charge: KSh 5,000.',
        ],
    ];

    public function run(): void
    {
        $branches = Branch::query()->where('is_active', true)->get();

        if ($branches->isEmpty()) {
            $this->command?->warn('No active branches found. Run BranchSeeder first.');

            return;
        }

        foreach ($branches as $branch) {
            $category = ServiceCategory::query()->updateOrCreate(
                [
                    'branch_id' => $branch->id,
                    'name' => 'Carpet',
                ],
                [
                    'description' => 'Carpet cleaning services by type and size',
                    'sort_order' => 2,
                    'is_active' => true,
                ],
            );

            foreach ($this->services as $service) {
                Service::query()->updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'name' => $service['name'],
                    ],
                    [
                        'service_category_id' => $category->id,
                        'description' => $service['description'],
                        'price' => $service['price'],
                        'duration_minutes' => $service['duration_minutes'],
                        'is_active' => true,
                    ],
                );
            }

            $this->command?->info("Seeded {$branch->name}: ".count($this->services).' carpet services.');
        }
    }
}
