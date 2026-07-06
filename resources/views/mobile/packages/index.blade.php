<x-layouts.mobile title="Packages">
    <x-mobile.page-header title="Packages" :back="route('mobile.menu')" />
    <div class="asp-mobile-list">
        @forelse ($packages as $package)
            <div class="asp-mobile-card">
                <p class="font-semibold">{{ $package->name }}</p>
                <p class="text-sm text-slate-500">KES {{ number_format($package->price, 0) }}</p>
            </div>
        @empty
            <x-ui.empty-state title="No packages" />
        @endforelse
    </div>
    <div class="mt-4">{{ $packages->links() }}</div>
</x-layouts.mobile>
