<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class CarWashServiceSeeder extends Seeder
{
    /**
     * @var array<int, array{
     *     name: string,
     *     price: float,
     *     commission_rate: float,
     *     target_revenue: float,
     *     commission_at_target: float,
     *     duration_minutes: int,
     * }>
     */
    protected array $services = [
        [
            'name' => 'ENGINE WASH',
            'price' => 500,
            'commission_rate' => 20,
            'target_revenue' => 1000,
            'commission_at_target' => 200,
            'duration_minutes' => 45,
        ],
        [
            'name' => 'VACUUM CLEANING',
            'price' => 500,
            'commission_rate' => 20,
            'target_revenue' => 500,
            'commission_at_target' => 100,
            'duration_minutes' => 30,
        ],
        [
            'name' => 'BODY WASH-SALOON',
            'price' => 400,
            'commission_rate' => 20,
            'target_revenue' => 400,
            'commission_at_target' => 80,
            'duration_minutes' => 30,
        ],
        [
            'name' => 'MOTORBIKE WASH',
            'price' => 200,
            'commission_rate' => 20,
            'target_revenue' => 200,
            'commission_at_target' => 40,
            'duration_minutes' => 20,
        ],
        [
            'name' => 'SIMPLE INTERIOR / SHAMPOO-SALOON',
            'price' => 2500,
            'commission_rate' => 20,
            'target_revenue' => 0,
            'commission_at_target' => 0,
            'duration_minutes' => 120,
        ],
        [
            'name' => 'BODY WASH-UBER',
            'price' => 300,
            'commission_rate' => 20,
            'target_revenue' => 0,
            'commission_at_target' => 0,
            'duration_minutes' => 25,
        ],
        [
            'name' => 'DASH BOARD SPRAY',
            'price' => 500,
            'commission_rate' => 20,
            'target_revenue' => 0,
            'commission_at_target' => 0,
            'duration_minutes' => 15,
        ],
        [
            'name' => 'BODY WASH- SUV AND 4 X 4',
            'price' => 500,
            'commission_rate' => 20,
            'target_revenue' => 1000,
            'commission_at_target' => 200,
            'duration_minutes' => 40,
        ],
        [
            'name' => 'BUFFING-SALOON',
            'price' => 4500,
            'commission_rate' => 25,
            'target_revenue' => 0,
            'commission_at_target' => 0,
            'duration_minutes' => 180,
        ],
        [
            'name' => 'BUFFING-SUV AND 4 X 4',
            'price' => 5500,
            'commission_rate' => 25,
            'target_revenue' => 0,
            'commission_at_target' => 0,
            'duration_minutes' => 210,
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
                    'name' => 'Car Wash',
                ],
                [
                    'description' => 'Exterior, interior, and detailing services',
                    'sort_order' => 1,
                    'is_active' => true,
                ],
            );

            foreach ($this->services as $index => $service) {
                Service::query()->updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'name' => $service['name'],
                    ],
                    [
                        'service_category_id' => $category->id,
                        'description' => $this->buildDescription($service),
                        'price' => $service['price'],
                        'duration_minutes' => $service['duration_minutes'],
                        'is_active' => true,
                    ],
                );
            }

            $this->command?->info("Seeded {$branch->name}: ".count($this->services).' services.');
        }
    }

    /**
     * @param  array{
     *     name: string,
     *     price: float,
     *     commission_rate: float,
     *     target_revenue: float,
     *     commission_at_target: float,
     *     duration_minutes: int,
     * }  $service
     */
    protected function buildDescription(array $service): string
    {
        $lines = [
            sprintf('Commission rate: %s%%', rtrim(rtrim(number_format($service['commission_rate'], 2), '0'), '.')),
        ];

        if ($service['target_revenue'] > 0) {
            $lines[] = sprintf(
                'Target revenue: KES %s (commission KES %s)',
                number_format($service['target_revenue'], 0),
                number_format($service['commission_at_target'], 0),
            );
        }

        return implode('. ', $lines).'.';
    }
}
