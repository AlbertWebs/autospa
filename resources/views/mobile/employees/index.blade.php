<x-layouts.mobile title="Employees">
    <x-mobile.page-header title="Employees" :back="route('mobile.menu')" />
    <div class="asp-mobile-list md:grid md:grid-cols-2 md:gap-3 md:space-y-0">
        @forelse ($employees as $employee)
            <div class="asp-mobile-card">
                <p class="font-semibold">{{ $employee->displayName() }}</p>
                <p class="text-sm text-slate-500">{{ $employee->position ?? 'Staff' }}</p>
            </div>
        @empty
            <x-ui.empty-state title="No employees" />
        @endforelse
    </div>
    <div class="mt-4">{{ $employees->links() }}</div>
</x-layouts.mobile>
