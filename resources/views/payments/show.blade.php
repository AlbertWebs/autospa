<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Payment #{{ $payment->id }}</h1></x-slot>

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('payments.index'),
        ])
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Customer</dt><dd class="font-medium">{{ $payment->customer?->full_name ?? 'N/A' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Amount</dt><dd class="font-medium">{{ number_format($payment->amount ?? 0, 2) }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Method</dt><dd><x-ui.badge color="indigo">{{ $payment->method ?? $payment->payment_method?->name ?? 'N/A' }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Reference</dt><dd>{{ $payment->reference ?? 'N/A' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Date</dt><dd>{{ $payment->created_at?->format('M j, Y g:i A') }}</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
