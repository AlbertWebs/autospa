@php
    use App\Enums\EmployeeType;

    $employee = $employee ?? null;
    $selectedType = old('employee_type', $employee->employee_type?->value ?? EmployeeType::Attendee->value);
@endphp

<x-ui.form-section title="Employee Information" description="Choose the staff type, then add contact and pay details.">
    <div class="asp-form-grid" x-data="{ employeeType: @js($selectedType) }">
        <x-ui.form-field label="Employee Type" for="employee_type" name="employee_type" :required="true" :col-span="2">
            <x-ui.select id="employee_type" name="employee_type" x-model="employeeType" required>
                @foreach (EmployeeType::options() as $value => $label)
                    <option value="{{ $value }}" @selected($selectedType === $value)>{{ $label }}</option>
                @endforeach
            </x-ui.select>
            <p class="asp-field-hint" x-show="employeeType === 'supervisor'" x-cloak>
                Supervisors receive a fixed salary.
            </p>
            <p class="asp-field-hint" x-show="employeeType === 'attendee'" x-cloak>
                Attendees wash vehicles and are paid commission per job.
            </p>
        </x-ui.form-field>

        <x-ui.form-field label="Employee Number" for="employee_number">
            @if ($employee)
                <x-ui.input id="employee_number" :value="$employee->employee_number" readonly disabled />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Auto-generated and cannot be changed.</p>
            @else
                <x-ui.input id="employee_number" :value="$nextEmployeeNumber ?? ''" readonly disabled />
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Assigned automatically when you save.</p>
            @endif
        </x-ui.form-field>

        <x-ui.form-field label="Full Name" for="full_name" name="full_name" :required="true">
            <x-ui.input id="full_name" name="full_name" :value="old('full_name', $employee->full_name ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Phone" for="phone" name="phone" hint="Required for M-Pesa commission payouts.">
            <x-ui.input id="phone" name="phone" type="tel" :value="old('phone', $employee->phone ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Email" for="email" name="email">
            <x-ui.input id="email" name="email" type="email" :value="old('email', $employee->email ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Base Salary" for="base_salary" name="base_salary">
            <x-ui.input
                id="base_salary"
                name="base_salary"
                type="number"
                step="0.01"
                min="0"
                :value="old('base_salary', $employee->base_salary ?? '')"
                x-bind:required="employeeType === 'supervisor'"
                x-bind:disabled="employeeType === 'attendee'"
                x-bind:placeholder="employeeType === 'attendee' ? 'Commission only' : '0.00'"
            />
            <p class="asp-field-hint" x-show="employeeType === 'supervisor'" x-cloak>Monthly salary for this supervisor.</p>
            <p class="asp-field-hint" x-show="employeeType === 'attendee'" x-cloak>Leave blank — attendees are paid commission only.</p>
        </x-ui.form-field>

        <x-ui.form-field label="Hire Date" for="hire_date" name="hire_date">
            <x-ui.input id="hire_date" name="hire_date" type="date" :value="old('hire_date', isset($employee->hire_date) ? $employee->hire_date->format('Y-m-d') : '')" />
        </x-ui.form-field>

        <x-ui.form-field name="is_active" :col-span="2">
            <x-ui.checkbox name="is_active" :checked="old('is_active', $employee->is_active ?? true)">Active</x-ui.checkbox>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
