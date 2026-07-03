<?php

namespace App\Http\Requests;

use App\Enums\JobCardStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'assigned_to' => ['nullable', 'exists:employees,id'],
            'status' => ['nullable', Rule::enum(JobCardStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
