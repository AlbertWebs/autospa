<x-layouts.mobile title="Categories">
    <x-mobile.page-header title="Service Categories" :back="route('mobile.menu')" />
    <div class="asp-mobile-list">
        @forelse ($categories as $category)
            <div class="asp-mobile-card">
                <p class="font-semibold">{{ $category->name }}</p>
            </div>
        @empty
            <x-ui.empty-state title="No categories" />
        @endforelse
    </div>
    <div class="mt-4">{{ $categories->links() }}</div>
</x-layouts.mobile>
