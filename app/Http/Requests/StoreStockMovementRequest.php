<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockMovementRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        if (! $this->filled('moved_at')) {
            $this->merge([
                'moved_at' => now()->format('Y-m-d\TH:i'),
            ]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'type' => ['required', 'string', 'in:in,out,adjustment'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'moved_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
            'return_to' => ['nullable', 'string', 'in:products,stock-movements'],
        ];
    }
}
