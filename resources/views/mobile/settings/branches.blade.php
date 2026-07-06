<x-layouts.mobile title="Branches">
    <x-mobile.page-header title="Branches" :back="route('mobile.menu')" />
    <div class="asp-mobile-list">
        @forelse ($branches as $branch)
            <div class="asp-mobile-card">
                <p class="font-semibold">{{ $branch->name }}</p>
                <p class="text-sm text-slate-500">{{ $branch->address }}</p>
            </div>
        @empty
            <x-ui.empty-state title="No branches" />
        @endforelse
    </div>
    <a href="{{ route('settings.branches.index') }}" class="asp-mobile-action-btn mt-4 inline-flex w-full justify-center">Manage branches</a>
</x-layouts.mobile>
