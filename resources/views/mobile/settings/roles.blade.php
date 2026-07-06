<x-layouts.mobile title="Roles">
    <x-mobile.page-header title="Roles" :back="route('mobile.menu')" />
    <div class="asp-mobile-list">
        @forelse ($roles as $role)
            <div class="asp-mobile-card">
                <p class="font-semibold">{{ $role->name }}</p>
                <p class="text-sm text-slate-500">{{ $role->slug }}</p>
            </div>
        @empty
            <x-ui.empty-state title="No roles" />
        @endforelse
    </div>
</x-layouts.mobile>
