@php
    use App\Enums\JobCardStatus;

    $jobCardsJson = $jobCards->map(fn ($jobCard) => [
        'id' => $jobCard->id,
        'registration_number' => $jobCard->vehicle?->registration_number ?? 'No vehicle assigned',
        'vehicle_summary' => trim(implode(' ', array_filter([
            $jobCard->vehicle?->make,
            $jobCard->vehicle?->model,
        ]))) ?: 'Vehicle details unavailable',
        'customer_name' => $jobCard->customer?->full_name ?? 'Walk-in',
        'assignee_name' => $jobCard->assignee?->displayName() ?? 'Unassigned',
        'status' => $jobCard->status?->value ?? JobCardStatus::Open->value,
        'started_at_human' => $jobCard->started_at?->diffForHumans(),
        'view_url' => route('job-cards.show', $jobCard),
        'update_url' => route('job-cards.live-status', $jobCard),
    ])->values();
@endphp

<x-ui.index-page
    eyebrow="Operations"
    title="Live"
    subtitle="Track wash bay activity in real time and move vehicles through the wash stages."
    :create-route="route('job-cards.create')"
    create-label="New Job Card"
>
    <div
        x-data="liveJobBoard({
            jobCards: @js($jobCardsJson),
            canManage: @js(auth()->user()?->can('permission', 'job-cards.manage') ?? false),
        })"
    >
        <div class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-ui.card>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Live Cars</p>
                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white" x-text="jobCards.length"></p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Queued</p>
                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white" x-text="countByStatus('open')"></p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Washing</p>
                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white" x-text="countByStatus('in_progress')"></p>
            </x-ui.card>
            <x-ui.card>
                <p class="text-xs font-semibold uppercase tracking-widest text-slate-400">Unassigned</p>
                <p class="mt-2 text-3xl font-bold text-slate-900 dark:text-white" x-text="jobCards.filter((jobCard) => jobCard.assignee_name === 'Unassigned').length"></p>
            </x-ui.card>
        </div>

        <div x-show="jobCards.length === 0" x-cloak>
            <x-ui.empty-state
                title="No live wash jobs"
                description="Cars waiting for wash or currently being washed will appear here."
            />
        </div>

        <div class="grid gap-5 xl:grid-cols-2" x-show="jobCards.length > 0" x-cloak>
            <template x-for="jobCard in jobCards" :key="jobCard.id">
                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="font-mono text-xs uppercase tracking-widest text-slate-400" x-text="`Job #${jobCard.id}`"></p>
                            <h2 class="mt-1 text-xl font-semibold text-slate-900 dark:text-white" x-text="jobCard.registration_number"></h2>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400" x-text="jobCard.vehicle_summary"></p>
                        </div>

                        <span
                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium"
                            x-bind:class="{
                                'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200': jobCard.status === 'open',
                                'bg-sky-100 text-sky-800 dark:bg-sky-900 dark:text-sky-200': jobCard.status === 'in_progress',
                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': jobCard.status === 'completed',
                                'bg-slate-100 text-slate-800 dark:bg-slate-900 dark:text-slate-200': !['open', 'in_progress', 'completed'].includes(jobCard.status),
                            }"
                            x-text="labelFor(jobCard.status)"
                        ></span>
                    </div>

                    <div class="mt-5 space-y-3 text-sm">
                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500 dark:text-slate-400">Customer</span>
                            <span class="font-medium text-slate-900 dark:text-white" x-text="jobCard.customer_name"></span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500 dark:text-slate-400">Assigned To</span>
                            <span class="font-medium text-slate-900 dark:text-white" x-text="jobCard.assignee_name"></span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span class="text-slate-500 dark:text-slate-400">Wash Stage</span>
                            <span class="font-medium text-slate-900 dark:text-white" x-text="`${progressFor(jobCard.status)}%`"></span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="h-2.5 overflow-hidden rounded-full bg-slate-200 dark:bg-slate-800">
                            <div
                                class="h-full rounded-full bg-indigo-500 transition-all"
                                x-bind:style="`width: ${progressFor(jobCard.status)}%;`"
                            ></div>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400">
                            <span x-text="startedLabel(jobCard)"></span>
                            <a :href="jobCard.view_url" class="font-medium text-brand-primary-dim hover:underline dark:text-brand-primary">
                                View details
                            </a>
                        </div>
                    </div>

                    <div class="mt-5 border-t border-slate-200 pt-4 dark:border-slate-800" x-show="canManage">
                        <p class="mb-3 text-xs font-semibold uppercase tracking-widest text-slate-400">Change Washing Status</p>
                        <div class="flex flex-wrap gap-2">
                            <template x-for="status in ['open', 'in_progress', 'completed']" :key="status">
                                <button
                                    type="button"
                                    x-bind:class="buttonClass(status)"
                                    x-bind:disabled="jobCard.status === status || isUpdating(jobCard.id)"
                                    @click="updateStatus(jobCard.id, status)"
                                >
                                    <span x-text="status === 'open' ? 'Queued' : (status === 'in_progress' ? 'Washing' : 'Ready')"></span>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</x-ui.index-page>
