<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethodType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PosStkPushRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'method' => ['required', Rule::in([PaymentMethodType::Mpesa->value])],
            'phone' => ['required', 'string', 'max:50'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ];
    }
}
