@php
    use App\Enums\JobCardStatus;
@endphp

<x-layouts.mobile title="Job Cards">
    <x-mobile.page-header
        title="Job Cards"
        subtitle="Today on the floor"
        :action-href="auth()->user()?->hasAnyPermission(['job-cards.manage']) ? route('mobile.job-cards.create') : null"
        action-label="New"
    />

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('mobile.job-cards.index', ['section' => 'open']) }}"
            @class(['asp-mobile-chip', 'asp-mobile-chip--active' => $section === 'open'])">
            Open ({{ $counts['open'] }})
        </a>
        <a href="{{ route('mobile.job-cards.index', ['section' => 'in_progress']) }}"
            @class(['asp-mobile-chip', 'asp-mobile-chip--active' => $section === 'in_progress'])">
            In Progress ({{ $counts['in_progress'] }})
        </a>
        <a href="{{ route('mobile.job-cards.index', ['section' => 'completed']) }}"
            @class(['asp-mobile-chip', 'asp-mobile-chip--active' => $section === 'completed'])">
            Completed ({{ $counts['completed'] }})
        </a>
    </div>

    <div class="asp-mobile-list md:grid md:grid-cols-2 md:gap-3 md:space-y-0">
        @forelse ($jobCards as $jobCard)
            <x-mobile.list-card
                :href="route('mobile.job-cards.show', $jobCard)"
                :title="$jobCard->vehicle?->registration_number ?? 'Job #' . $jobCard->id"
                :subtitle="$jobCard->customer?->full_name ?? 'Walk-in'"
                :meta="$jobCard->assignee?->displayName() ?? 'Unassigned'"
                :status="$jobCard->status?->label()"
            />
        @empty
            <x-ui.empty-state title="No job cards" description="No vehicles in this status for today." />
        @endforelse
    </div>
</x-layouts.mobile>
