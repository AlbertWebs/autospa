<x-layouts.mobile title="Commissions">
    <x-mobile.page-header title="Commissions" :back="route('mobile.menu')" />
    <div class="asp-mobile-list">
        @forelse ($commissions as $commission)
            <div class="asp-mobile-card text-sm">
                <p class="font-semibold">{{ $commission->employee?->displayName() }}</p>
                <p class="text-slate-500">KES {{ number_format($commission->amount, 0) }} · {{ $commission->earned_on?->format('M j, Y') }}</p>
            </div>
        @empty
            <x-ui.empty-state title="No commissions" />
        @endforelse
    </div>
    <div class="mt-4">{{ $commissions->links() }}</div>
</x-layouts.mobile>
