@php $employee = $employee ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="user_id" value="Linked User" />
        <select id="user_id" name="user_id" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">
            <option value="">None</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}" @selected(old('user_id', $employee->user_id ?? '') == $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('user_id')" />
    </div>
    <div>
        <x-input-label for="employee_number" value="Employee Number" />
        <x-text-input id="employee_number" name="employee_number" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('employee_number', $employee->employee_number ?? '')" required />
        <x-input-error :messages="$errors->get('employee_number')" />
    </div>
    <div>
        <x-input-label for="full_name" value="Full Name" />
        <x-text-input id="full_name" name="full_name" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('full_name', $employee->full_name ?? '')" required />
        <x-input-error :messages="$errors->get('full_name')" />
    </div>
    <div>
        <x-input-label for="position" value="Position" />
        <x-text-input id="position" name="position" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('position', $employee->position ?? '')" />
        <x-input-error :messages="$errors->get('position')" />
    </div>
    <div>
        <x-input-label for="phone" value="Phone" />
        <x-text-input id="phone" name="phone" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('phone', $employee->phone ?? '')" />
        <x-input-error :messages="$errors->get('phone')" />
    </div>
    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('email', $employee->email ?? '')" />
        <x-input-error :messages="$errors->get('email')" />
    </div>
    <div>
        <x-input-label for="base_salary" value="Base Salary" />
        <x-text-input id="base_salary" name="base_salary" type="number" step="0.01" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('base_salary', $employee->base_salary ?? '')" />
        <x-input-error :messages="$errors->get('base_salary')" />
    </div>
    <div>
        <x-input-label for="hire_date" value="Hire Date" />
        <x-text-input id="hire_date" name="hire_date" type="date" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('hire_date', isset($employee->hire_date) ? $employee->hire_date->format('Y-m-d') : '')" />
        <x-input-error :messages="$errors->get('hire_date')" />
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('is_active', $employee->is_active ?? true))>
            <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
        </label>
    </div>
</div>
