<x-layouts.mobile title="Performance">
    <x-mobile.page-header title="Performance" :back="route('mobile.menu')" />
    <div class="asp-mobile-list">
        @forelse ($metrics as $metric)
            <div class="asp-mobile-card text-sm">
                <p class="font-semibold">{{ $metric->employee?->displayName() }}</p>
                <p class="text-slate-500">{{ $metric->jobs_completed }} jobs · KES {{ number_format($metric->revenue_generated, 0) }}</p>
            </div>
        @empty
            <x-ui.empty-state title="No performance data" />
        @endforelse
    </div>
    <div class="mt-4">{{ $metrics->links() }}</div>
</x-layouts.mobile>
