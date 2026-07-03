@php $attendance = $attendance ?? null; @endphp

<x-ui.form-section title="Attendance Record" description="Employee attendance, clock times, and status.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Employee" for="employee_id" name="employee_id" :required="true" :col-span="2">
            <x-ui.select id="employee_id" name="employee_id" required>
                <option value="">Select employee…</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}" @selected(old('employee_id', $attendance->employee_id ?? '') == $employee->id)>{{ $employee->full_name }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Date" for="date" name="date" :required="true">
            <x-ui.input id="date" name="date" type="date" :value="old('date', isset($attendance->date) ? $attendance->date->format('Y-m-d') : now()->format('Y-m-d'))" required />
        </x-ui.form-field>

        <x-ui.form-field label="Status" for="status" name="status" :required="true">
            <x-ui.select id="status" name="status" required>
                @foreach (['present', 'absent', 'late', 'half_day', 'leave'] as $status)
                    <option value="{{ $status }}" @selected(old('status', $attendance->status ?? 'present') == $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Clock In" for="clock_in" name="clock_in">
            <x-ui.input id="clock_in" name="clock_in" type="time" :value="old('clock_in', $attendance->clock_in ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Clock Out" for="clock_out" name="clock_out">
            <x-ui.input id="clock_out" name="clock_out" type="time" :value="old('clock_out', $attendance->clock_out ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Notes" for="notes" name="notes" :col-span="2">
            <x-ui.textarea id="notes" name="notes" rows="3">{{ old('notes', $attendance->notes ?? '') }}</x-ui.textarea>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
