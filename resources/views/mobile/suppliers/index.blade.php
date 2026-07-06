<x-layouts.mobile title="Suppliers">
    <x-mobile.page-header title="Suppliers" :back="route('mobile.menu')" />
    <div class="asp-mobile-list md:grid md:grid-cols-2 md:gap-3 md:space-y-0">
        @forelse ($suppliers as $supplier)
            <div class="asp-mobile-card">
                <p class="font-semibold">{{ $supplier->name }}</p>
                <p class="text-sm text-slate-500">{{ $supplier->phone ?? $supplier->email }}</p>
            </div>
        @empty
            <x-ui.empty-state title="No suppliers" />
        @endforelse
    </div>
    <div class="mt-4">{{ $suppliers->links() }}</div>
</x-layouts.mobile>
