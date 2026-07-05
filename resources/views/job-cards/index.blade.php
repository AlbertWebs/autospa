@php
    use App\Enums\JobCardStatus;

    $statusBadgeColor = fn (JobCardStatus $status): string => match ($status) {
        JobCardStatus::Open => 'amber',
        JobCardStatus::InProgress => 'sky',
        JobCardStatus::Completed => 'green',
        JobCardStatus::Cancelled => 'slate',
    };

    $sections = [
        [
            'id' => 'open',
            'title' => 'Open Jobs',
            'description' => 'New job cards awaiting assignment or start.',
            'jobCards' => $openJobCards,
            'emptyTitle' => 'No open job cards',
            'emptyDescription' => 'Open job cards will appear here.',
        ],
        [
            'id' => 'in_progress',
            'title' => 'In Progress',
            'description' => 'Vehicles currently being washed or serviced.',
            'jobCards' => $inProgressJobCards,
            'emptyTitle' => 'No jobs in progress',
            'emptyDescription' => 'Active job cards will appear here.',
        ],
        [
            'id' => 'completed',
            'title' => 'Completed',
            'description' => 'Recently finished job cards.',
            'jobCards' => $completedJobCards,
            'emptyTitle' => 'No completed job cards',
            'emptyDescription' => 'Completed job cards will appear here.',
        ],
    ];
@endphp

<x-ui.index-page
    eyebrow="Operations"
    title="Job Cards"
    subtitle="Today's open, in progress, and completed jobs — {{ $today->format('l, M j, Y') }}."
    :create-route="route('job-cards.create')"
    create-label="New Job Card"
>
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <x-ui.stat-card label="Open" :value="$counts['open']" icon="hourglass_top" variant="bookings" />
        <x-ui.stat-card label="In Progress" :value="$counts['in_progress']" icon="autorenew" variant="service" />
        <x-ui.stat-card label="Completed" :value="$counts['completed']" icon="task_alt" variant="ready" />
    </div>

    <div class="mb-6 flex flex-wrap gap-2">
        @foreach ($sections as $section)
            <a
                href="#{{ $section['id'] }}"
                class="rounded-xl border border-slate-200/80 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-brand-primary/40 hover:text-brand-primary-dim dark:border-brand-border/60 dark:bg-brand-surface-high dark:text-slate-200 dark:hover:text-brand-primary"
            >
                {{ $section['title'] }}
                <span class="ml-1 font-mono text-xs text-slate-400">({{ $counts[$section['id']] ?? $section['jobCards']->count() }})</span>
            </a>
        @endforeach
    </div>

    <div class="space-y-8">
        @foreach ($sections as $section)
            <section id="{{ $section['id'] }}" class="scroll-mt-24">
                <div class="mb-4">
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $section['title'] }}</h2>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ $section['description'] }}</p>
                </div>

                <x-ui.data-table
                    :empty="$section['jobCards']->isEmpty()"
                    :empty-title="$section['emptyTitle']"
                    :empty-description="$section['emptyDescription']"
                    :count="$section['jobCards']->count()"
                >
                    <x-slot name="header">
                        <x-ui.th>#</x-ui.th>
                        <x-ui.th>Customer</x-ui.th>
                        <x-ui.th>Vehicle</x-ui.th>
                        <x-ui.th>Assignee</x-ui.th>
                        <x-ui.th>Status</x-ui.th>
                        <x-ui.th align="right">Actions</x-ui.th>
                    </x-slot>

                    @foreach ($section['jobCards'] as $jobCard)
                        <tr class="asp-table-row">
                            <x-ui.td mono primary>#{{ $jobCard->id }}</x-ui.td>
                            <x-ui.td>{{ $jobCard->customer?->full_name ?? 'N/A' }}</x-ui.td>
                            <x-ui.td mono>{{ $jobCard->vehicle?->registration_number ?? 'N/A' }}</x-ui.td>
                            <x-ui.td>{{ $jobCard->assignee?->displayName() ?? 'Unassigned' }}</x-ui.td>
                            <x-ui.td>
                                <x-ui.badge :color="$statusBadgeColor($jobCard->status)">{{ $jobCard->status->label() }}</x-ui.badge>
                            </x-ui.td>
                            <x-ui.td align="right">
                                <x-ui.table-actions
                                    :view="route('job-cards.show', $jobCard)"
                                    :edit="route('job-cards.edit', $jobCard)"
                                />
                            </x-ui.td>
                        </tr>
                    @endforeach
                </x-ui.data-table>
            </section>
        @endforeach
    </div>
</x-ui.index-page>
