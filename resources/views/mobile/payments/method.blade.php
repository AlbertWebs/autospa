<x-layouts.mobile :title="$title">
    <x-mobile.page-header :title="$title" :back="route('mobile.payments.index')" />
    <div class="asp-mobile-list">
        @forelse ($payments as $payment)
            <div class="asp-mobile-card text-sm">
                <p class="font-semibold">KES {{ number_format($payment->amount, 0) }}</p>
                <p class="text-slate-500">{{ $payment->invoice?->invoice_number ?? 'Payment #' . $payment->id }}</p>
            </div>
        @empty
            <x-ui.empty-state :title="'No ' . strtolower($title)" />
        @endforelse
    </div>
    <div class="mt-4">{{ $payments->links() }}</div>
</x-layouts.mobile>
