<?php

namespace App\Http\Requests;

use App\Services\TestDataPurgeService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurgeTestDataRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isSuperAdmin();
    }

    public function rules(): array
    {
        $groupKeys = array_keys(app(TestDataPurgeService::class)->catalog());

        return [
            'groups' => ['required', 'array', 'min:1'],
            'groups.*' => ['string', Rule::in($groupKeys)],
            'password' => ['required', 'current_password'],
            'confirm' => ['accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'groups.required' => 'Select at least one data group to delete.',
            'groups.min' => 'Select at least one data group to delete.',
            'confirm.accepted' => 'Confirm that you understand this cannot be undone.',
        ];
    }
}
