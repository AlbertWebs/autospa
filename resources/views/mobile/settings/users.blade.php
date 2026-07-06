<x-layouts.mobile title="Users">
    <x-mobile.page-header title="Users" :back="route('mobile.menu')" />
    <div class="asp-mobile-list">
        @forelse ($users as $user)
            <div class="asp-mobile-card">
                <p class="font-semibold">{{ $user->name }}</p>
                <p class="text-sm text-slate-500">{{ $user->email }}</p>
            </div>
        @empty
            <x-ui.empty-state title="No users" />
        @endforelse
    </div>
    <div class="mt-4">{{ $users->links() }}</div>
</x-layouts.mobile>
