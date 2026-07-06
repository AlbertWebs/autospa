<x-layouts.mobile title="Services">
    <x-mobile.page-header title="Services" :back="route('mobile.menu')" />
    <div class="asp-mobile-list md:grid md:grid-cols-2 md:gap-3 md:space-y-0">
        @forelse ($services as $service)
            <div class="asp-mobile-card">
                <p class="font-semibold">{{ $service->name }}</p>
                <p class="text-sm text-slate-500">KES {{ number_format($service->price, 0) }} · {{ $service->category?->name }}</p>
            </div>
        @empty
            <x-ui.empty-state title="No services" />
        @endforelse
    </div>
    <div class="mt-4">{{ $services->links() }}</div>
</x-layouts.mobile>
