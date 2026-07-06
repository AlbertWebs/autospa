<?php

namespace App\Http\Requests\Setup;

use App\Support\CommissionSettings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Services\InstallService;

class SetupPreferencesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return ! app(InstallService::class)->isInstalled();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'sms_notifications_enabled' => $this->boolean('sms_notifications_enabled'),
            'commissions_enabled' => $this->boolean('commissions_enabled'),
            'seed_car_wash_services' => $this->boolean('seed_car_wash_services'),
        ]);
    }

    public function rules(): array
    {
        return [
            'sms_notifications_enabled' => ['nullable', 'boolean'],
            'commissions_enabled' => ['nullable', 'boolean'],
            'commission_default_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'commission_trigger' => [
                'nullable',
                'string',
                Rule::in([
                    CommissionSettings::TRIGGER_JOB_COMPLETED,
                    CommissionSettings::TRIGGER_POS_CHECKOUT,
                    CommissionSettings::TRIGGER_BOTH,
                ]),
            ],
            'seed_car_wash_services' => ['nullable', 'boolean'],
        ];
    }
}
