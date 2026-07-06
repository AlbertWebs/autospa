<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Sales</span></x-slot>

    <x-ui.section-header eyebrow="Sales" />

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('refunds.index'),
        ])
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Amount</dt><dd class="font-medium">{{ number_format($refund->amount ?? 0, 2) }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-ui.badge color="indigo">{{ $refund->status ?? 'pending' }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Reason</dt><dd>{{ $refund->reason ?? 'N/A' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Date</dt><dd>{{ $refund->created_at?->format('M j, Y') }}</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
