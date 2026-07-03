@php $attendance = $attendance ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="employee_id" value="Employee" />
        <select id="employee_id" name="employee_id" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
            <option value="">Select employee…</option>
            @foreach ($employees as $employee)
                <option value="{{ $employee->id }}" @selected(old('employee_id', $attendance->employee_id ?? '') == $employee->id)>{{ $employee->full_name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('employee_id')" />
    </div>
    <div>
        <x-input-label for="date" value="Date" />
        <x-text-input id="date" name="date" type="date" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('date', isset($attendance->date) ? $attendance->date->format('Y-m-d') : now()->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('date')" />
    </div>
    <div>
        <x-input-label for="status" value="Status" />
        <select id="status" name="status" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" required>
            @foreach (['present', 'absent', 'late', 'half_day', 'leave'] as $status)
                <option value="{{ $status }}" @selected(old('status', $attendance->status ?? 'present') == $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" />
    </div>
    <div>
        <x-input-label for="clock_in" value="Clock In" />
        <x-text-input id="clock_in" name="clock_in" type="time" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('clock_in', $attendance->clock_in ?? '')" />
        <x-input-error :messages="$errors->get('clock_in')" />
    </div>
    <div>
        <x-input-label for="clock_out" value="Clock Out" />
        <x-text-input id="clock_out" name="clock_out" type="time" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white" :value="old('clock_out', $attendance->clock_out ?? '')" />
        <x-input-error :messages="$errors->get('clock_out')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="notes" value="Notes" />
        <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full rounded-xl border-slate-200 bg-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:border-slate-700 dark:bg-slate-900 dark:text-white">{{ old('notes', $attendance->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" />
    </div>
</div>
