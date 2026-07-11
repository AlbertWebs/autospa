<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PublicBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('registration_number') === '') {
            $this->merge(['registration_number' => null]);
        }

        if ($this->input('email') === '') {
            $this->merge(['email' => null]);
        }

        if ($this->input('notes') === '') {
            $this->merge(['notes' => null]);
        }
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:20'],
            'scheduled_at' => ['required', 'date', 'after:now'],
            'service_ids' => ['required', 'array', 'min:1'],
            'service_ids.*' => ['integer', 'exists:services,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'website' => ['nullable', 'max:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'scheduled_at.after' => 'Please choose a future date and time.',
            'service_ids.required' => 'Select at least one service.',
            'service_ids.min' => 'Select at least one service.',
            'website.max' => 'Unable to submit this booking request.',
        ];
    }
}
