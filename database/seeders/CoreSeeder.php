<?php

namespace Database\Seeders;

use App\Enums\PaymentMethodType;
use App\Models\Integration;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Models\Tax;
use Illuminate\Database\Seeder;

class CoreSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $settings = [
            ['group' => 'receipt', 'key' => 'footer_text', 'value' => 'Thank you for your business!'],
            ['group' => 'receipt', 'key' => 'show_logo', 'value' => 'true'],
            ['group' => 'sms', 'key' => 'enabled', 'value' => 'false'],
            ['group' => 'sms', 'key' => 'sender_id', 'value' => 'AUTOSPA'],
            ['group' => 'commission', 'key' => 'enabled', 'value' => 'false'],
            ['group' => 'commission', 'key' => 'default_rate', 'value' => '0.30'],
            ['group' => 'commission', 'key' => 'trigger', 'value' => 'job_completed'],
            ['group' => 'loyalty', 'key' => 'enabled', 'value' => 'true'],
            ['group' => 'loyalty', 'key' => 'washes_before_free', 'value' => '10'],
            ['group' => 'attendance', 'key' => 'enabled', 'value' => 'false'],
            ['group' => 'email', 'key' => 'from_name', 'value' => 'AutoSpa'],
            ['group' => 'email', 'key' => 'notifications_enabled', 'value' => 'true'],
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
                [
                    'driver' => $provider === 'sms' ? 'rebuetext' : 'stub',
                    'is_enabled' => false,
                    'credentials' => $provider === 'sms' && env('REBUETEXT_ACCESS_TOKEN')
                        ? ['access_token' => env('REBUETEXT_ACCESS_TOKEN')]
                        : null,
                    'settings' => $provider === 'sms'
                        ? ['sender_id' => env('REBUETEXT_SENDER_ID', 'AUTOSPA')]
                        : null,
                ]
            );
        }
    }
}
