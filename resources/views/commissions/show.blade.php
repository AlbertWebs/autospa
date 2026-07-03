<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Commission #{{ $commission->id }}</h1></x-slot>

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('commissions.index'),
        ])
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Employee</dt><dd class="font-medium">{{ $commission->employee?->full_name ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Amount</dt><dd class="font-medium">{{ number_format($commission->amount ?? 0, 2) }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Rate</dt><dd>{{ $commission->rate ? number_format($commission->rate * 100, 2).'%' : '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-ui.badge color="indigo">{{ ucfirst($commission->status ?? 'pending') }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Earned On</dt><dd>{{ $commission->earned_on?->format('M j, Y') ?? '—' }}</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
