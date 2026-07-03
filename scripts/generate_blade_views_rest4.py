#!/usr/bin/env python3
"""Generate remaining AutoSpa Blade view files (part 2d: employees through profile)."""
import os, sys
sys.path.insert(0, os.path.dirname(__file__))
from generate_blade_views import IC, write, created, layout, create_page, edit_page, show_page, show_page_view_only

# ============ EMPLOYEES ============
write("employees/_form.blade.php", f"""@php $employee = $employee ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="user_id" value="Linked User" />
        <select id="user_id" name="user_id" class="{IC}">
            <option value="">None</option>
            @foreach ($users as $user)
                <option value="{{{{ $user->id }}}}" @selected(old('user_id', $employee->user_id ?? '') == $user->id)>{{{{ $user->name }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('user_id')" />
    </div>
    <div>
        <x-input-label for="employee_number" value="Employee Number" />
        <x-text-input id="employee_number" name="employee_number" class="{IC}" :value="old('employee_number', $employee->employee_number ?? '')" required />
        <x-input-error :messages="$errors->get('employee_number')" />
    </div>
    <div>
        <x-input-label for="full_name" value="Full Name" />
        <x-text-input id="full_name" name="full_name" class="{IC}" :value="old('full_name', $employee->full_name ?? '')" required />
        <x-input-error :messages="$errors->get('full_name')" />
    </div>
    <div>
        <x-input-label for="position" value="Position" />
        <x-text-input id="position" name="position" class="{IC}" :value="old('position', $employee->position ?? '')" />
        <x-input-error :messages="$errors->get('position')" />
    </div>
    <div>
        <x-input-label for="phone" value="Phone" />
        <x-text-input id="phone" name="phone" class="{IC}" :value="old('phone', $employee->phone ?? '')" />
        <x-input-error :messages="$errors->get('phone')" />
    </div>
    <div>
        <x-input-label for="email" value="Email" />
        <x-text-input id="email" name="email" type="email" class="{IC}" :value="old('email', $employee->email ?? '')" />
        <x-input-error :messages="$errors->get('email')" />
    </div>
    <div>
        <x-input-label for="base_salary" value="Base Salary" />
        <x-text-input id="base_salary" name="base_salary" type="number" step="0.01" class="{IC}" :value="old('base_salary', $employee->base_salary ?? '')" />
        <x-input-error :messages="$errors->get('base_salary')" />
    </div>
    <div>
        <x-input-label for="hire_date" value="Hire Date" />
        <x-text-input id="hire_date" name="hire_date" type="date" class="{IC}" :value="old('hire_date', isset($employee->hire_date) ? $employee->hire_date->format('Y-m-d') : '')" />
        <x-input-error :messages="$errors->get('hire_date')" />
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('is_active', $employee->is_active ?? true))>
            <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
        </label>
    </div>
</div>""")

write("employees/index.blade.php", """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Employees</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('employees.create'), 'createLabel' => 'Add Employee'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">Employees</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Employee #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Position</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($employees as $employee)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $employee->full_name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $employee->employee_number }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $employee->position ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">@if($employee->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('employees.show', $employee) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('employees.edit', $employee) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($employees->isEmpty())<x-ui.empty-state title="No employees yet" description="Add staff members to manage your team." />@endif
        @include('partials.crud.pagination', ['paginator' => $employees])
    </x-ui.card>
</x-layouts.app>""")

write("employees/create.blade.php", create_page("Add Employee", "employees.store", "employees.index", "employees._form", "Create Employee"))
write("employees/edit.blade.php", edit_page("Edit Employee", "employees.update", "employees.show", "employees._form", "employee", "Save Changes"))
write("employees/show.blade.php", show_page("{{ $employee->full_name }}", "employees.index", "employees.edit", "employees.destroy", "Delete this employee?", """    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Employee #</dt><dd class="font-medium">{{ $employee->employee_number }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Position</dt><dd>{{ $employee->position ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Phone</dt><dd>{{ $employee->phone ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Email</dt><dd>{{ $employee->email ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Hire Date</dt><dd>{{ $employee->hire_date?->format('M j, Y') ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($employee->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
        </dl>
    </x-ui.card>"""))

# ============ ATTENDANCE ============
write("attendance/_form.blade.php", f"""@php $attendance = $attendance ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="employee_id" value="Employee" />
        <select id="employee_id" name="employee_id" class="{IC}" required>
            <option value="">Select employee…</option>
            @foreach ($employees as $employee)
                <option value="{{{{ $employee->id }}}}" @selected(old('employee_id', $attendance->employee_id ?? '') == $employee->id)>{{{{ $employee->full_name }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('employee_id')" />
    </div>
    <div>
        <x-input-label for="date" value="Date" />
        <x-text-input id="date" name="date" type="date" class="{IC}" :value="old('date', isset($attendance->date) ? $attendance->date->format('Y-m-d') : now()->format('Y-m-d'))" required />
        <x-input-error :messages="$errors->get('date')" />
    </div>
    <div>
        <x-input-label for="status" value="Status" />
        <select id="status" name="status" class="{IC}" required>
            @foreach (['present', 'absent', 'late', 'half_day', 'leave'] as $status)
                <option value="{{{{ $status }}}}" @selected(old('status', $attendance->status ?? 'present') == $status)>{{{{ ucfirst(str_replace('_', ' ', $status)) }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" />
    </div>
    <div>
        <x-input-label for="clock_in" value="Clock In" />
        <x-text-input id="clock_in" name="clock_in" type="time" class="{IC}" :value="old('clock_in', $attendance->clock_in ?? '')" />
        <x-input-error :messages="$errors->get('clock_in')" />
    </div>
    <div>
        <x-input-label for="clock_out" value="Clock Out" />
        <x-text-input id="clock_out" name="clock_out" type="time" class="{IC}" :value="old('clock_out', $attendance->clock_out ?? '')" />
        <x-input-error :messages="$errors->get('clock_out')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="notes" value="Notes" />
        <textarea id="notes" name="notes" rows="3" class="{IC}">{{ old('notes', $attendance->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" />
    </div>
</div>""")

write("attendance/index.blade.php", """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Attendance</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('attendance.create'), 'createLabel' => 'Record Attendance'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">Attendance</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Employee</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Clock In/Out</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($attendance as $record)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $record->employee?->full_name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $record->date?->format('M j, Y') }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $record->clock_in ?? '—' }} / {{ $record->clock_out ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ ucfirst(str_replace('_', ' ', $record->status)) }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('attendance.show', $record) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($attendance->isEmpty())<x-ui.empty-state title="No attendance records" description="Record employee attendance here." />@endif
        @include('partials.crud.pagination', ['paginator' => $attendance])
    </x-ui.card>
</x-layouts.app>""")

write("attendance/create.blade.php", create_page("Record Attendance", "attendance.store", "attendance.index", "attendance._form", "Save Attendance"))
write("attendance/show.blade.php", show_page_view_only("Attendance — {{ $attendance->employee?->full_name }}", "attendance.index", """    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Date</dt><dd class="font-medium">{{ $attendance->date?->format('M j, Y') }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Clock In</dt><dd>{{ $attendance->clock_in ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Clock Out</dt><dd>{{ $attendance->clock_out ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-ui.badge color="indigo">{{ ucfirst(str_replace('_', ' ', $attendance->status)) }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Notes</dt><dd>{{ $attendance->notes ?? '—' }}</dd></div>
        </dl>
    </x-ui.card>"""))

# ============ COMMISSIONS & PERFORMANCE ============
write("commissions/index.blade.php", """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Commissions</h1></x-slot>
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Employee</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Period</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($commissions as $commission)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $commission->employee?->full_name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ number_format($commission->amount ?? 0, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $commission->period ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ $commission->status ?? 'pending' }}</x-ui.badge></td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($commissions->isEmpty())<x-ui.empty-state title="No commissions" description="Staff commission records will appear here." />@endif
        @include('partials.crud.pagination', ['paginator' => $commissions])
    </x-ui.card>
</x-layouts.app>""")

write("performance/index.blade.php", """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Staff Performance</h1></x-slot>
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card title="Jobs Completed" :value="$metrics['jobs_completed'] ?? 0" />
        <x-ui.stat-card title="Revenue Generated" :value="number_format($metrics['revenue'] ?? 0, 2)" />
        <x-ui.stat-card title="Avg. Rating" :value="$metrics['avg_rating'] ?? '—'" />
        <x-ui.stat-card title="Attendance Rate" :value="($metrics['attendance_rate'] ?? 0).'%'" />
    </div>
    <x-ui.card class="mt-6" :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Employee</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Jobs</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Revenue</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Rating</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($metrics['employees'] ?? [] as $row)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $row['name'] ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $row['jobs'] ?? 0 }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ number_format($row['revenue'] ?? 0, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $row['rating'] ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-6 py-8"><x-ui.empty-state title="No performance data" description="Staff performance metrics will appear here." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-ui.card>
</x-layouts.app>""")

# ============ REPORTS ============
def report_page(title, period_label):
    return f"""<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{title}</h1></x-slot>
    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <x-ui.stat-card title="Revenue" :value="number_format($report['revenue'] ?? 0, 2)" />
        <x-ui.stat-card title="Bookings" :value="$report['bookings'] ?? 0" />
        <x-ui.stat-card title="{period_label}" :value="$report['period'] ?? ($report['date'] ?? '—')" />
        <x-ui.stat-card title="Customers" :value="$report['customers'] ?? 0" />
    </div>
    <x-ui.card>
        <h2 class="mb-4 text-lg font-semibold">Summary</h2>
        <dl class="grid gap-4 sm:grid-cols-2 text-sm">
            @foreach ($report as $key => $value)
                @if(!is_array($value))
                    <div class="flex justify-between gap-4 rounded-lg bg-slate-50 px-4 py-3 dark:bg-slate-800">
                        <dt class="text-slate-500 capitalize">{{{{ str_replace('_', ' ', $key) }}}}</dt>
                        <dd class="font-medium">{{{{ is_numeric($value) && str_contains($key, 'revenue') ? number_format($value, 2) : $value }}}}</dd>
                    </div>
                @endif
            @endforeach
        </dl>
    </x-ui.card>
</x-layouts.app>"""

write("reports/daily.blade.php", report_page("Daily Report", "Date"))
write("reports/weekly.blade.php", report_page("Weekly Report", "Week"))
write("reports/monthly.blade.php", report_page("Monthly Report", "Month"))
write("reports/revenue.blade.php", report_page("Revenue Report", "Period"))
write("reports/customers.blade.php", report_page("Customer Report", "Period"))
write("reports/staff.blade.php", report_page("Staff Report", "Period"))
write("reports/inventory.blade.php", report_page("Inventory Report", "Period"))

# ============ PROMOTIONS ============
write("promotions/_form.blade.php", f"""@php $promotion = $promotion ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div>
        <x-input-label for="name" value="Promotion Name" />
        <x-text-input id="name" name="name" class="{IC}" :value="old('name', $promotion->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>
    <div>
        <x-input-label for="code" value="Promo Code" />
        <x-text-input id="code" name="code" class="{IC}" :value="old('code', $promotion->code ?? '')" required />
        <x-input-error :messages="$errors->get('code')" />
    </div>
    <div>
        <x-input-label for="type" value="Type" />
        <select id="type" name="type" class="{IC}" required>
            @foreach (['percentage', 'fixed'] as $type)
                <option value="{{{{ $type }}}}" @selected(old('type', $promotion->type ?? 'percentage') == $type)>{{{{ ucfirst($type) }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('type')" />
    </div>
    <div>
        <x-input-label for="value" value="Value" />
        <x-text-input id="value" name="value" type="number" step="0.01" class="{IC}" :value="old('value', $promotion->value ?? '')" required />
        <x-input-error :messages="$errors->get('value')" />
    </div>
    <div>
        <x-input-label for="starts_at" value="Starts At" />
        <x-text-input id="starts_at" name="starts_at" type="datetime-local" class="{IC}" :value="old('starts_at', isset($promotion->starts_at) ? $promotion->starts_at->format('Y-m-d\\TH:i') : '')" />
        <x-input-error :messages="$errors->get('starts_at')" />
    </div>
    <div>
        <x-input-label for="ends_at" value="Ends At" />
        <x-text-input id="ends_at" name="ends_at" type="datetime-local" class="{IC}" :value="old('ends_at', isset($promotion->ends_at) ? $promotion->ends_at->format('Y-m-d\\TH:i') : '')" />
        <x-input-error :messages="$errors->get('ends_at')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="description" value="Description" />
        <textarea id="description" name="description" rows="3" class="{IC}">{{ old('description', $promotion->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" />
    </div>
    <div class="sm:col-span-2">
        <label class="flex items-center gap-2">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 dark:border-slate-600" @checked(old('is_active', $promotion->is_active ?? true))>
            <span class="text-sm text-slate-700 dark:text-slate-300">Active</span>
        </label>
    </div>
</div>""")

write("promotions/index.blade.php", """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Promotions</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('promotions.create'), 'createLabel' => 'Add Promotion'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">Promotions</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Value</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($promotions as $promotion)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $promotion->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ $promotion->code }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $promotion->type === 'percentage' ? $promotion->value.'%' : number_format($promotion->value, 2) }}</td>
                            <td class="whitespace-nowrap px-6 py-4">@if($promotion->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('promotions.show', $promotion) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('promotions.edit', $promotion) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($promotions->isEmpty())<x-ui.empty-state title="No promotions yet" description="Create promo codes to attract customers." />@endif
        @include('partials.crud.pagination', ['paginator' => $promotions])
    </x-ui.card>
</x-layouts.app>""")

write("promotions/create.blade.php", create_page("Add Promotion", "promotions.store", "promotions.index", "promotions._form", "Create Promotion"))
write("promotions/edit.blade.php", edit_page("Edit Promotion", "promotions.update", "promotions.show", "promotions._form", "promotion", "Save Changes"))
write("promotions/show.blade.php", show_page("{{ $promotion->name }}", "promotions.index", "promotions.edit", "promotions.destroy", "Delete this promotion?", """    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Code</dt><dd><x-ui.badge color="indigo">{{ $promotion->code }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Type / Value</dt><dd>{{ ucfirst($promotion->type) }} — {{ $promotion->type === 'percentage' ? $promotion->value.'%' : number_format($promotion->value, 2) }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Valid</dt><dd>{{ $promotion->starts_at?->format('M j, Y') ?? '—' }} — {{ $promotion->ends_at?->format('M j, Y') ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($promotion->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Description</dt><dd>{{ $promotion->description ?? '—' }}</dd></div>
        </dl>
    </x-ui.card>"""))

# ============ SMS CAMPAIGNS ============
write("marketing/sms-campaigns/_form.blade.php", f"""@php $campaign = $campaign ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="name" value="Campaign Name" />
        <x-text-input id="name" name="name" class="{IC}" :value="old('name', $campaign->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="message" value="Message" />
        <textarea id="message" name="message" rows="4" class="{IC}" required>{{ old('message', $campaign->message ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('message')" />
    </div>
    <div>
        <x-input-label for="status" value="Status" />
        <select id="status" name="status" class="{IC}" required>
            @foreach (['draft', 'scheduled', 'sent', 'cancelled'] as $status)
                <option value="{{{{ $status }}}}" @selected(old('status', $campaign->status ?? 'draft') == $status)>{{{{ ucfirst($status) }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" />
    </div>
    <div>
        <x-input-label for="scheduled_at" value="Scheduled At" />
        <x-text-input id="scheduled_at" name="scheduled_at" type="datetime-local" class="{IC}" :value="old('scheduled_at', isset($campaign->scheduled_at) ? $campaign->scheduled_at->format('Y-m-d\\TH:i') : '')" />
        <x-input-error :messages="$errors->get('scheduled_at')" />
    </div>
</div>""")

write("marketing/sms-campaigns/index.blade.php", """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">SMS Campaigns</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('marketing.sms.create'), 'createLabel' => 'New SMS Campaign'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">SMS Campaigns</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Scheduled</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($campaigns as $campaign)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $campaign->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ ucfirst($campaign->status) }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $campaign->scheduled_at?->format('M j, Y g:i A') ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('marketing.sms.show', $campaign) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('marketing.sms.edit', $campaign) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($campaigns->isEmpty())<x-ui.empty-state title="No SMS campaigns" description="Create SMS campaigns to reach customers." />@endif
        @include('partials.crud.pagination', ['paginator' => $campaigns])
    </x-ui.card>
</x-layouts.app>""")

write("marketing/sms-campaigns/create.blade.php", create_page("New SMS Campaign", "marketing.sms.store", "marketing.sms.index", "marketing.sms-campaigns._form", "Create Campaign"))
write("marketing/sms-campaigns/edit.blade.php", edit_page("Edit SMS Campaign", "marketing.sms.update", "marketing.sms.show", "marketing.sms-campaigns._form", "campaign", "Save Changes"))
write("marketing/sms-campaigns/show.blade.php", show_page("{{ $campaign->name }}", "marketing.sms.index", "marketing.sms.edit", "marketing.sms.destroy", "Delete this campaign?", """    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-ui.badge color="indigo">{{ ucfirst($campaign->status) }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Scheduled</dt><dd>{{ $campaign->scheduled_at?->format('M j, Y g:i A') ?? '—' }}</dd></div>
            <div><dt class="mb-1 text-slate-500">Message</dt><dd class="rounded-lg bg-slate-50 p-3 dark:bg-slate-800">{{ $campaign->message }}</dd></div>
        </dl>
    </x-ui.card>"""))

# ============ EMAIL CAMPAIGNS ============
write("marketing/email-campaigns/_form.blade.php", f"""@php $campaign = $campaign ?? null; @endphp
<div class="grid gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <x-input-label for="name" value="Campaign Name" />
        <x-text-input id="name" name="name" class="{IC}" :value="old('name', $campaign->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="subject" value="Subject" />
        <x-text-input id="subject" name="subject" class="{IC}" :value="old('subject', $campaign->subject ?? '')" required />
        <x-input-error :messages="$errors->get('subject')" />
    </div>
    <div class="sm:col-span-2">
        <x-input-label for="body" value="Email Body" />
        <textarea id="body" name="body" rows="8" class="{IC}" required>{{ old('body', $campaign->body ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('body')" />
    </div>
    <div>
        <x-input-label for="status" value="Status" />
        <select id="status" name="status" class="{IC}" required>
            @foreach (['draft', 'scheduled', 'sent', 'cancelled'] as $status)
                <option value="{{{{ $status }}}}" @selected(old('status', $campaign->status ?? 'draft') == $status)>{{{{ ucfirst($status) }}}}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('status')" />
    </div>
    <div>
        <x-input-label for="scheduled_at" value="Scheduled At" />
        <x-text-input id="scheduled_at" name="scheduled_at" type="datetime-local" class="{IC}" :value="old('scheduled_at', isset($campaign->scheduled_at) ? $campaign->scheduled_at->format('Y-m-d\\TH:i') : '')" />
        <x-input-error :messages="$errors->get('scheduled_at')" />
    </div>
</div>""")

write("marketing/email-campaigns/index.blade.php", """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Email Campaigns</h1></x-slot>
    @include('partials.crud.index-header', ['createRoute' => route('marketing.email.create'), 'createLabel' => 'New Email Campaign'])
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">Email Campaigns</h1>
    @endinclude
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-slate-500">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($campaigns as $campaign)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $campaign->name }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ Str::limit($campaign->subject, 40) }}</td>
                            <td class="whitespace-nowrap px-6 py-4"><x-ui.badge color="indigo">{{ ucfirst($campaign->status) }}</x-ui.badge></td>
                            <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                                <a href="{{ route('marketing.email.show', $campaign) }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View</a>
                                <span class="mx-2 text-slate-300">|</span>
                                <a href="{{ route('marketing.email.edit', $campaign) }}" class="text-slate-600 hover:text-slate-900 dark:text-slate-400">Edit</a>
                            </td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($campaigns->isEmpty())<x-ui.empty-state title="No email campaigns" description="Create email campaigns to engage customers." />@endif
        @include('partials.crud.pagination', ['paginator' => $campaigns])
    </x-ui.card>
</x-layouts.app>""")

write("marketing/email-campaigns/create.blade.php", create_page("New Email Campaign", "marketing.email.store", "marketing.email.index", "marketing.email-campaigns._form", "Create Campaign"))
write("marketing/email-campaigns/edit.blade.php", edit_page("Edit Email Campaign", "marketing.email.update", "marketing.email.show", "marketing.email-campaigns._form", "campaign", "Save Changes"))
write("marketing/email-campaigns/show.blade.php", show_page("{{ $campaign->name }}", "marketing.email.index", "marketing.email.edit", "marketing.email.destroy", "Delete this campaign?", """    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Subject</dt><dd class="font-medium">{{ $campaign->subject }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-ui.badge color="indigo">{{ ucfirst($campaign->status) }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Scheduled</dt><dd>{{ $campaign->scheduled_at?->format('M j, Y g:i A') ?? '—' }}</dd></div>
            <div><dt class="mb-1 text-slate-500">Body</dt><dd class="prose prose-sm max-w-none rounded-lg bg-slate-50 p-4 dark:bg-slate-800">{!! nl2br(e($campaign->body)) !!}</dd></div>
        </dl>
    </x-ui.card>"""))

# ============ LOYALTY, NOTIFICATIONS, PROFILE ============
write("marketing/loyalty.blade.php", layout("Loyalty Program", """    <div class="grid gap-6 lg:grid-cols-3">
        <x-ui.stat-card title="Total Members" value="—" />
        <x-ui.stat-card title="Points Issued" value="—" />
        <x-ui.stat-card title="Points Redeemed" value="—" />
    </div>
    <x-ui.card class="mt-6">
        <h2 class="mb-4 text-lg font-semibold">Loyalty Program Settings</h2>
        <p class="text-sm text-slate-500">Configure your loyalty program rules and rewards. Visit customer loyalty transactions for detailed history.</p>
        <div class="mt-4">
            <a href="{{ route('customers.loyalty') }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400">View loyalty transactions →</a>
        </div>
    </x-ui.card>"""))

write("notifications/index.blade.php", """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Notifications</h1></x-slot>
    <x-ui.card :padding="false">
        <div class="divide-y divide-slate-200 dark:divide-slate-800">
            @forelse ($notifications as $notification)
                <div class="flex gap-4 px-6 py-4 {{ $notification->read_at ? '' : 'bg-indigo-50/50 dark:bg-indigo-950/20' }}">
                    <div class="flex-1">
                        <p class="font-medium">{{ $notification->data['title'] ?? 'Notification' }}</p>
                        <p class="text-sm text-slate-500">{{ $notification->data['message'] ?? '' }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ $notification->created_at?->diffForHumans() }}</p>
                    </div>
                    @unless($notification->read_at)
                        <x-ui.badge color="indigo">New</x-ui.badge>
                    @endunless
                </div>
            @empty
                <x-ui.empty-state title="No notifications" description="You're all caught up!" />
            @endforelse
        </div>
        @include('partials.crud.pagination', ['paginator' => $notifications])
    </x-ui.card>
</x-layouts.app>""")

write("profile/edit.blade.php", """<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Profile</h1></x-slot>

    <div class="space-y-6">
        <x-ui.card>
            @include('profile.partials.update-profile-information-form')
        </x-ui.card>

        <x-ui.card>
            @include('profile.partials.update-password-form')
        </x-ui.card>

        <x-ui.card>
            @include('profile.partials.delete-user-form')
        </x-ui.card>
    </div>
</x-layouts.app>""")

print(f"Part 2d done, total: {len(created)}")
