<x-layouts.mobile title="Attendance">
    <x-mobile.page-header title="Attendance" :back="route('mobile.menu')" />
    <div class="asp-mobile-list">
        @forelse ($attendance as $record)
            <div class="asp-mobile-card text-sm">
                <p class="font-semibold">{{ $record->employee?->displayName() }}</p>
                <p class="text-slate-500">{{ $record->date?->format('M j, Y') }} · {{ $record->status }}</p>
            </div>
        @empty
            <x-ui.empty-state title="No attendance records" />
        @endforelse
    </div>
    <div class="mt-4">{{ $attendance->links() }}</div>
</x-layouts.mobile>
