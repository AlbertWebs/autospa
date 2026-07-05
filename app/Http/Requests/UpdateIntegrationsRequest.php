<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class UpdateIntegrationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', Setting::class);
    }

    public function rules(): array
    {
        return [
            'integrations' => ['required', 'array'],
            'integrations.*.enabled' => ['nullable', 'boolean'],
            'integrations.*.driver' => ['nullable', 'string', 'max:50'],
            'integrations.*.access_token' => ['nullable', 'string', 'max:5000'],
            'integrations.*.sender_id' => ['nullable', 'string', 'max:11'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $integrations = collect($this->input('integrations', []))
            ->map(function (array $integration): array {
                $integration['enabled'] = filter_var(
                    $integration['enabled'] ?? false,
                    FILTER_VALIDATE_BOOLEAN
                );

                return $integration;
            })
            ->all();

        $this->merge(['integrations' => $integrations]);
    }
}
