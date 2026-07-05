<?php

namespace App\Http\Requests\Setup;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SetupAdminRequest extends SetupStepRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }
}
