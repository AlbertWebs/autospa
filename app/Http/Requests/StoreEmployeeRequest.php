<?php

namespace App\Http\Requests;

use App\Enums\EmployeeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee_type' => ['required', Rule::enum(EmployeeType::class)],
            'employee_number' => ['nullable', 'string', 'max:50'],
            'full_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'base_salary' => [
                Rule::requiredIf(fn () => $this->input('employee_type') === EmployeeType::Supervisor->value),
                'nullable',
                'numeric',
                'min:0',
            ],
            'hire_date' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $employeeType = EmployeeType::tryFrom((string) $this->input('employee_type'));

        if ($employeeType) {
            $this->merge([
                'position' => $employeeType->label(),
            ]);
        }

        if ($this->input('employee_type') === EmployeeType::Attendee->value && $this->input('base_salary') === '') {
            $this->merge(['base_salary' => null]);
        }
    }

    /** @return array<string, mixed> */
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        if (($validated['employee_type'] ?? null) === EmployeeType::Attendee->value) {
            $validated['base_salary'] = $validated['base_salary'] ?? null;
        }

        $validated['position'] = EmployeeType::from($validated['employee_type'])->label();

        return $validated;
    }
}
