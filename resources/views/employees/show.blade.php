<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $employee->full_name }}</h1></x-slot>

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('employees.index'),
            'editRoute' => route('employees.edit', $employee),
            'deleteRoute' => route('employees.destroy', $employee),
            'deleteConfirm' => 'Delete this employee?',
        ])
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Employee #</dt><dd class="font-medium">{{ $employee->employee_number }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Position</dt><dd>{{ $employee->position ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Phone</dt><dd>{{ $employee->phone ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Email</dt><dd>{{ $employee->email ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Hire Date</dt><dd>{{ $employee->hire_date?->format('M j, Y') ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($employee->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
