<x-layouts.mobile title="Payment Methods">
    <x-mobile.page-header title="Payment Methods" :back="route('mobile.menu')" />
    <div class="asp-mobile-list">
        @forelse ($paymentMethods as $method)
            <div class="asp-mobile-card">
                <p class="font-semibold">{{ $method->name }}</p>
                <p class="text-sm text-slate-500">{{ $method->slug }}</p>
            </div>
        @empty
            <x-ui.empty-state title="No payment methods" />
        @endforelse
    </div>
</x-layouts.mobile>
