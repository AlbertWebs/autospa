<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    public function run(): void
    {
        Branch::updateOrCreate(
            ['code' => 'HQ'],
            [
                'name' => 'AutoSpa Headquarters',
                'address' => '123 Main Street, Nairobi',
                'phone' => '+254700000001',
                'email' => 'hq@autospa.test',
                'is_active' => true,
            ]
        );

        Branch::updateOrCreate(
            ['code' => 'WEST'],
            [
                'name' => 'AutoSpa Westlands',
                'address' => '45 Westlands Road, Nairobi',
                'phone' => '+254700000002',
                'email' => 'west@autospa.test',
                'is_active' => true,
            ]
        );
    }
}
