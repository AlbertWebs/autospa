<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Integration;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\Tax;
use App\Enums\PaymentMethodType;
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

        $settings = [
            ['group' => 'receipt', 'key' => 'footer_text', 'value' => 'Thank you for choosing AutoSpa!'],
            ['group' => 'receipt', 'key' => 'show_logo', 'value' => 'true'],
            ['group' => 'sms', 'key' => 'enabled', 'value' => 'false'],
            ['group' => 'sms', 'key' => 'sender_id', 'value' => 'AUTOSPA'],
            ['group' => 'email', 'key' => 'from_name', 'value' => 'AutoSpa'],
        ];

        foreach ($settings as $setting) {
            Setting::updateOrCreate(
                ['branch_id' => null, 'group' => $setting['group'], 'key' => $setting['key']],
                ['value' => $setting['value'], 'type' => 'string']
            );
        }

        foreach (PaymentMethodType::cases() as $method) {
            PaymentMethod::updateOrCreate(
                ['branch_id' => null, 'slug' => $method->value],
                ['name' => $method->label(), 'is_active' => true]
            );
        }

        Tax::updateOrCreate(
            ['branch_id' => null, 'code' => 'VAT'],
            ['name' => 'VAT', 'rate' => 16, 'is_active' => true, 'is_default' => true]
        );

        foreach (['mpesa', 'sms', 'whatsapp', 'google_maps', 'google_calendar'] as $provider) {
            Integration::updateOrCreate(
                ['branch_id' => null, 'provider' => $provider],
                ['driver' => 'stub', 'is_enabled' => false]
            );
        }
    }
}
