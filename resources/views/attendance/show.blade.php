<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Staff</span></x-slot>

    <x-ui.section-header eyebrow="Staff" />

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('attendance.index'),
        ])
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Date</dt><dd class="font-medium">{{ $attendance->date?->format('M j, Y') }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Clock In</dt><dd>{{ $attendance->clock_in ?? 'N/A' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Clock Out</dt><dd>{{ $attendance->clock_out ?? 'N/A' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-ui.badge color="indigo">{{ ucfirst(str_replace('_', ' ', $attendance->status)) }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Notes</dt><dd>{{ $attendance->notes ?? 'N/A' }}</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
