<?php

namespace App\Http\Requests;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Http\Requests\Concerns\NormalizesBookingServices;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBookingRequest extends FormRequest
{
    use NormalizesBookingServices;
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'type' => ['required', Rule::enum(BookingType::class)],
            'status' => ['nullable', Rule::enum(BookingStatus::class)],
            'scheduled_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:scheduled_at'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_recurring' => ['boolean'],
            'services' => ['array'],
            'services.*.service_id' => ['required', 'exists:services,id'],
            'services.*.price' => ['required', 'numeric', 'min:0'],
            'services.*.duration_minutes' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
