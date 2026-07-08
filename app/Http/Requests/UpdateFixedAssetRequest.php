<?php

namespace App\Http\Requests;

use App\Enums\FixedAssetCategory;
use App\Enums\FixedAssetStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateFixedAssetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(FixedAssetCategory::class)],
            'description' => ['nullable', 'string', 'max:2000'],
            'location' => ['nullable', 'string', 'max:255'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'assigned_employee_id' => ['nullable', 'exists:employees,id'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_cost' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(FixedAssetStatus::class)],
            'is_active' => ['boolean'],
        ];
    }
}
