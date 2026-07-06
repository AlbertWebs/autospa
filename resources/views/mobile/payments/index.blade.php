<x-layouts.mobile title="Payments">
    <x-mobile.page-header title="Payments" :back="route('mobile.menu')" />
    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('mobile.payments.cash') }}" class="asp-mobile-chip">Cash</a>
        <a href="{{ route('mobile.payments.mpesa') }}" class="asp-mobile-chip">M-Pesa</a>
        <a href="{{ route('mobile.payments.card') }}" class="asp-mobile-chip">Card</a>
        <a href="{{ route('mobile.payments.bank') }}" class="asp-mobile-chip">Bank</a>
    </div>
    <div class="asp-mobile-list">
        @forelse ($payments as $payment)
            <div class="asp-mobile-card text-sm">
                <p class="font-semibold">KES {{ number_format($payment->amount, 0) }}</p>
                <p class="text-slate-500">{{ $payment->paymentMethod?->name ?? $payment->method?->label() }} · {{ $payment->paid_at?->format('M j, Y') }}</p>
            </div>
        @empty
            <x-ui.empty-state title="No payments" />
        @endforelse
    </div>
    <div class="mt-4">{{ $payments->links() }}</div>
</x-layouts.mobile>
