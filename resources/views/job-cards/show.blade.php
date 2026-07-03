<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Job Card #{{ $jobCard->id }}</h1></x-slot>

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('job-cards.index'),
            'editRoute' => route('job-cards.edit', $jobCard),
            'deleteRoute' => route('job-cards.destroy', $jobCard),
            'deleteConfirm' => 'Delete this job card?',
        ])
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Customer</dt><dd class="font-medium">{{ $jobCard->customer?->full_name ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Vehicle</dt><dd>{{ $jobCard->vehicle?->registration_number ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Booking</dt><dd>{{ $jobCard->booking_id ? '#'.$jobCard->booking_id : '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Assigned To</dt><dd>{{ $jobCard->assignee?->displayName() ?? 'Unassigned' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-ui.badge color="indigo">{{ $jobCard->status->label() }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Notes</dt><dd>{{ $jobCard->notes ?? '—' }}</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
