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
            'name' => 'Engine Wash',
            'price' => 500,
            'commission_rate' => 20,
            'target_revenue' => 1000,
            'commission_at_target' => 200,
            'duration_minutes' => 45,
        ],
        [
            'name' => 'Vacuum Cleaning',
            'price' => 500,
            'commission_rate' => 20,
            'target_revenue' => 500,
            'commission_at_target' => 100,
            'duration_minutes' => 30,
        ],
        [
            'name' => 'Body Wash-Saloon',
            'price' => 400,
            'commission_rate' => 20,
            'target_revenue' => 400,
            'commission_at_target' => 80,
            'duration_minutes' => 30,
        ],
        [
            'name' => 'Motorbike Wash',
            'price' => 200,
            'commission_rate' => 20,
            'target_revenue' => 200,
            'commission_at_target' => 40,
            'duration_minutes' => 20,
        ],
        [
            'name' => 'Simple Interior / Shampoo-Saloon',
            'price' => 2500,
            'commission_rate' => 20,
            'target_revenue' => 0,
            'commission_at_target' => 0,
            'duration_minutes' => 120,
        ],
        [
            'name' => 'Body Wash-Uber',
            'price' => 300,
            'commission_rate' => 20,
            'target_revenue' => 0,
            'commission_at_target' => 0,
            'duration_minutes' => 25,
        ],
        [
            'name' => 'Dash Board Spray',
            'price' => 500,
            'commission_rate' => 20,
            'target_revenue' => 0,
            'commission_at_target' => 0,
            'duration_minutes' => 15,
        ],
        [
            'name' => 'Body Wash-SUV and 4 X 4',
            'price' => 500,
            'commission_rate' => 20,
            'target_revenue' => 1000,
            'commission_at_target' => 200,
            'duration_minutes' => 40,
        ],
        [
            'name' => 'Buffing-Saloon',
            'price' => 4500,
            'commission_rate' => 25,
            'target_revenue' => 0,
            'commission_at_target' => 0,
            'duration_minutes' => 180,
        ],
        [
            'name' => 'Buffing-SUV and 4 X 4',
            'price' => 5500,
            'commission_rate' => 25,
            'target_revenue' => 0,
            'commission_at_target' => 0,
            'duration_minutes' => 210,
        ],
    ];

    /** @var array<string, string> New title-case name => legacy uppercase name */
    protected array $legacyNames = [
        'Engine Wash' => 'ENGINE WASH',
        'Vacuum Cleaning' => 'VACUUM CLEANING',
        'Body Wash-Saloon' => 'BODY WASH-SALOON',
        'Motorbike Wash' => 'MOTORBIKE WASH',
        'Simple Interior / Shampoo-Saloon' => 'SIMPLE INTERIOR / SHAMPOO-SALOON',
        'Body Wash-Uber' => 'BODY WASH-UBER',
        'Dash Board Spray' => 'DASH BOARD SPRAY',
        'Body Wash-SUV and 4 X 4' => 'BODY WASH- SUV AND 4 X 4',
        'Buffing-Saloon' => 'BUFFING-SALOON',
        'Buffing-SUV and 4 X 4' => 'BUFFING-SUV AND 4 X 4',
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
                $legacyName = $this->legacyNames[$service['name']] ?? null;

                $existing = Service::query()
                    ->where('branch_id', $branch->id)
                    ->where(function ($query) use ($service, $legacyName) {
                        $query->where('name', $service['name']);

                        if ($legacyName) {
                            $query->orWhere('name', $legacyName);
                        }
                    })
                    ->first();

                $attributes = [
                    'service_category_id' => $category->id,
                    'description' => $this->buildDescription($service),
                    'price' => $service['price'],
                    'duration_minutes' => $service['duration_minutes'],
                    'is_active' => true,
                    'name' => $service['name'],
                ];

                if ($existing) {
                    $existing->update($attributes);
                } else {
                    Service::query()->create(array_merge(
                        ['branch_id' => $branch->id],
                        $attributes,
                    ));
                }
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
