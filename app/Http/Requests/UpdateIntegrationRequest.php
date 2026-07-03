<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;

class UpdateIntegrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', Setting::class);
    }

    public function rules(): array
    {
        return [
            'driver' => ['required', 'string', 'max:50'],
            'is_enabled' => ['boolean'],
            'credentials' => ['nullable', 'array'],
            'settings' => ['nullable', 'array'],
        ];
    }
}
