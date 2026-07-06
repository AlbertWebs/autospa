<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Product;
use Illuminate\Database\Seeder;

class RetailProductSeeder extends Seeder
{
    /**
     * @var array<int, array{
     *     sku: string,
     *     name: string,
     *     category: string,
     *     cost_price: float,
     *     selling_price: float,
     *     quantity_on_hand: float,
     *     unit: string,
     * }>
     */
    protected array $products = [
        [
            'sku' => 'PRD 003',
            'name' => 'Brush',
            'category' => 'Accessories',
            'cost_price' => 450.00,
            'selling_price' => 520.00,
            'quantity_on_hand' => 15,
            'unit' => 'piece',
        ],
        [
            'sku' => 'PRD001',
            'name' => 'Dash board cleaner',
            'category' => 'CAR CARE',
            'cost_price' => 50.00,
            'selling_price' => 80.00,
            'quantity_on_hand' => 7,
            'unit' => 'piece',
        ],
        [
            'sku' => 'PRD 002',
            'name' => 'Key Holders',
            'category' => 'Accessories',
            'cost_price' => 200.00,
            'selling_price' => 250.00,
            'quantity_on_hand' => 15,
            'unit' => 'piece',
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
            foreach ($this->products as $product) {
                Product::query()->updateOrCreate(
                    [
                        'branch_id' => $branch->id,
                        'sku' => $product['sku'],
                    ],
                    [
                        'name' => $product['name'],
                        'description' => 'Category: '.$product['category'],
                        'unit' => $product['unit'],
                        'cost_price' => $product['cost_price'],
                        'selling_price' => $product['selling_price'],
                        'quantity_on_hand' => $product['quantity_on_hand'],
                        'minimum_level' => 0,
                        'is_active' => true,
                    ],
                );
            }

            $this->command?->info("Seeded {$branch->name}: ".count($this->products).' products.');
        }
    }
}
