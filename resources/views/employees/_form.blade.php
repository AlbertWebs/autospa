@php $employee = $employee ?? null; @endphp

<x-ui.form-section title="Employee Information" description="Basic details, contact information, and employment data.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Linked User" for="user_id" name="user_id">
            <x-ui.select id="user_id" name="user_id">
                <option value="">None</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(old('user_id', $employee->user_id ?? '') == $user->id)>{{ $user->name }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Employee Number" for="employee_number" name="employee_number" :required="true">
            <x-ui.input id="employee_number" name="employee_number" :value="old('employee_number', $employee->employee_number ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Full Name" for="full_name" name="full_name" :required="true">
            <x-ui.input id="full_name" name="full_name" :value="old('full_name', $employee->full_name ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Position" for="position" name="position">
            <x-ui.input id="position" name="position" :value="old('position', $employee->position ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Phone" for="phone" name="phone">
            <x-ui.input id="phone" name="phone" type="tel" :value="old('phone', $employee->phone ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Email" for="email" name="email">
            <x-ui.input id="email" name="email" type="email" :value="old('email', $employee->email ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Base Salary" for="base_salary" name="base_salary">
            <x-ui.input id="base_salary" name="base_salary" type="number" step="0.01" :value="old('base_salary', $employee->base_salary ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Hire Date" for="hire_date" name="hire_date">
            <x-ui.input id="hire_date" name="hire_date" type="date" :value="old('hire_date', isset($employee->hire_date) ? $employee->hire_date->format('Y-m-d') : '')" />
        </x-ui.form-field>

        <x-ui.form-field name="is_active" :col-span="2">
            <x-ui.checkbox name="is_active" :checked="old('is_active', $employee->is_active ?? true)">Active</x-ui.checkbox>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
