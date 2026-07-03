<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Receipt {{ $receipt->number ?? '#'.$receipt->id }}</h1></x-slot>

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('receipts.index'),
        ])
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Customer</dt><dd class="font-medium">{{ $receipt->customer?->full_name ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Amount</dt><dd class="font-medium">{{ number_format($receipt->total ?? 0, 2) }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Date</dt><dd>{{ $receipt->created_at?->format('M j, Y g:i A') }}</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
