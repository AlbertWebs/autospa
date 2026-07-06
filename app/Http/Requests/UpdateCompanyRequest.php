<?php

namespace App\Http\Requests;

use App\Models\Setting;
use App\Support\AttendanceSettings;
use App\Support\CommissionSettings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCompanyRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'sms_notifications_enabled' => $this->boolean('sms_notifications_enabled'),
            'commissions_enabled' => $this->boolean('commissions_enabled'),
            'loyalty_enabled' => $this->boolean('loyalty_enabled'),
            'attendance_enabled' => $this->boolean('attendance_enabled'),
        ]);
    }

    public function authorize(): bool
    {
        return $this->user()->can('update', Setting::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:100'],
            'tax_number' => ['nullable', 'string', 'max:100'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
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
            'loyalty_enabled' => ['nullable', 'boolean'],
            'loyalty_washes_before_free' => ['nullable', 'integer', 'min:1', 'max:100'],
            'attendance_enabled' => ['nullable', 'boolean'],
        ];
    }
}
