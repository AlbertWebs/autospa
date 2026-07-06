<?php

namespace App\Http\Requests\Setup;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SetupTeamRequest extends SetupStepRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'create_supervisor' => $this->boolean('create_supervisor'),
        ]);
    }

    public function rules(): array
    {
        return [
            'create_supervisor' => ['nullable', 'boolean'],
            'supervisor_name' => ['required_if:create_supervisor,1', 'nullable', 'string', 'max:255'],
            'supervisor_email' => [
                'required_if:create_supervisor,1',
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],
            'supervisor_password' => ['required_if:create_supervisor,1', 'nullable', 'confirmed', Password::defaults()],
        ];
    }
}
