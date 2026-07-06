<?php

namespace App\Http\Requests;

use App\Enums\JobCardStatus;
use App\Http\Requests\Concerns\ValidatesJobCardServices;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobCardRequest extends FormRequest
{
    use ValidatesJobCardServices;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'assigned_to' => ['nullable', 'exists:employees,id'],
            'status' => ['nullable', Rule::enum(JobCardStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        if ($this->routeIs('job-cards.update')) {
            $rules = array_merge($rules, $this->jobCardServiceRules(required: true));
        }

        return $rules;
    }
}
