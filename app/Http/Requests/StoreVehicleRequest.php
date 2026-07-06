<?php

namespace App\Http\Requests;

use App\Enums\VehicleStatus;
use App\Support\RegistrationNumber;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('registration_number')) {
            $this->merge([
                'registration_number' => RegistrationNumber::normalize($this->input('registration_number')),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'registration_number' => ['required', 'string', 'max:20'],
            'make' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'color' => ['nullable', 'string', 'max:50'],
            'vin' => ['nullable', 'string', 'max:50'],
            'mileage' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', Rule::enum(VehicleStatus::class)],
        ];
    }
}
