<?php

namespace App\Http\Requests;

use App\Enums\PaymentMethodType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class PosCheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'job_card_id' => ['nullable', 'exists:job_cards,id'],
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'method' => ['required', Rule::enum(PaymentMethodType::class)],
            'stk_phone' => ['nullable', 'string', 'max:50'],
            'stk_reference' => ['nullable', 'string', 'max:255'],
            'stk_status' => ['nullable', 'string', 'max:50'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'total_amount' => ['required', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type' => ['required', Rule::in(['service', 'product'])],
            'items.*.item_id' => ['nullable', 'integer'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.total' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled('job_card_id')) {
                return;
            }

            foreach ($this->input('items', []) as $index => $item) {
                if (($item['item_type'] ?? '') === 'service') {
                    $validator->errors()->add(
                        "items.{$index}.item_type",
                        'Add wash services on the job card, then checkout from the job card.',
                    );
                }
            }
        });
    }
}
