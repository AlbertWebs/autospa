<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Company::updateOrCreate(
            ['name' => 'AutoSpa Management System'],
            [
                'legal_name' => 'AutoSpa Kenya Ltd',
                'registration_number' => 'CPR/2024/001',
                'tax_number' => 'P051234567X',
                'address' => '123 Main Street, Nairobi, Kenya',
                'phone' => '+254700000000',
                'email' => 'info@autospa.test',
                'website' => 'https://autospa.test',
            ]
        );
    }
}
