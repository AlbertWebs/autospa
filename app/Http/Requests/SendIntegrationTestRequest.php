<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendIntegrationTestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', \App\Models\Setting::class) ?? false;
    }

    public function rules(): array
    {
        $channel = $this->input('channel');

        return [
            'channel' => ['required', Rule::in(['email', 'sms', 'whatsapp', 'mpesa'])],
            'recipient' => match ($channel) {
                'email' => ['required', 'email', 'max:255'],
                'sms', 'whatsapp', 'mpesa' => ['required', 'string', 'max:30'],
                default => ['required', 'string', 'max:255'],
            },
            'subject' => ['nullable', 'string', 'max:255'],
            'message' => [
                Rule::requiredIf(fn () => $this->input('channel') !== 'mpesa'),
                'nullable',
                'string',
                'max:1000',
            ],
            'amount' => [
                Rule::requiredIf(fn () => $this->input('channel') === 'mpesa'),
                'nullable',
                'numeric',
                'min:1',
                'max:100000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient.required' => 'Enter a recipient email or phone number.',
            'message.required' => 'Enter a test message.',
            'amount.required' => 'Enter a test amount for M-Pesa.',
        ];
    }
}
