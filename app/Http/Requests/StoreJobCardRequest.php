<?php

namespace App\Http\Requests;

use App\Enums\JobCardStatus;
use App\Http\Requests\Concerns\ValidatesJobCardServices;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobCardRequest extends FormRequest
{
    use ValidatesJobCardServices;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('vehicle_id') === '' || $this->input('vehicle_id') === null) {
            $this->merge(['vehicle_id' => null]);
        }
    }

    public function rules(): array
    {
        return array_merge([
            'customer_id' => ['required', 'exists:customers,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'assigned_to' => ['nullable', 'exists:employees,id'],
            'status' => ['nullable', Rule::enum(JobCardStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], $this->jobCardServiceRules(required: true));
    }
}
