<x-layouts.mobile title="Job Card #{{ $jobCard->id }}">
    <x-mobile.page-header
        :title="$jobCard->vehicle?->registration_number ?? 'Job #' . $jobCard->id"
        :subtitle="$jobCard->customer?->full_name ?? 'Walk-in'"
        :back="route('mobile.job-cards.live')"
    />

    <div class="space-y-4">
        <div class="asp-mobile-card space-y-3">
            <div class="flex justify-between text-sm">
                <span class="text-slate-500">Status</span>
                <span class="font-semibold">{{ $jobCard->status?->label() }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-slate-500">Logged by</span>
                <span class="font-semibold">{{ $jobCard->creator?->name ?? '—' }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-slate-500">Assigned to</span>
                <span class="font-semibold">{{ $jobCard->assignee?->displayName() ?? 'Unassigned' }}</span>
            </div>
            <div class="flex justify-between text-sm">
                <span class="text-slate-500">Vehicle</span>
                <span class="font-semibold">{{ trim(implode(' ', array_filter([$jobCard->vehicle?->make, $jobCard->vehicle?->model]))) ?: '—' }}</span>
            </div>
            @if ($jobCard->started_at)
                <div class="flex justify-between text-sm">
                    <span class="text-slate-500">Started</span>
                    <span>{{ $jobCard->started_at->format('M j, g:i A') }}</span>
                </div>
            @endif
        </div>

        @if ($jobCard->services->isNotEmpty())
            <div class="asp-mobile-card">
                <h3 class="mb-2 font-semibold">Services</h3>
                <ul class="space-y-2 text-sm">
                    @foreach ($jobCard->services as $line)
                        <li class="flex justify-between">
                            <span>{{ $line->service?->name ?? 'Service' }}</span>
                            <span>KES {{ number_format($line->price, 0) }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <a href="{{ route('job-cards.show', $jobCard) }}" class="asp-mobile-action-btn inline-flex w-full justify-center">
            Open full details
        </a>
    </div>
</x-layouts.mobile>
