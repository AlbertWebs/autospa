<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncPushRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mutations' => ['required', 'array', 'min:1', 'max:50'],
            'mutations.*.id' => ['required', 'uuid'],
            'mutations.*.type' => ['required', 'string', 'max:100'],
            'mutations.*.client_entity_uuid' => ['nullable', 'uuid'],
            'mutations.*.payload' => ['required', 'array'],
            'mutations.*.created_at' => ['nullable', 'date'],
        ];
    }
}
