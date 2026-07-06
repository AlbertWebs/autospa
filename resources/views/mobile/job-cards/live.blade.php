<x-layouts.mobile title="Live">
    <x-mobile.page-header
        title="Live"
        subtitle="{{ $stats['total'] }} vehicles on the floor"
        :action-href="auth()->user()?->hasAnyPermission(['job-cards.manage']) ? route('mobile.job-cards.create') : null"
        action-label="Check In"
    />

    <div class="mb-4 grid grid-cols-2 gap-2 md:grid-cols-4">
        <div class="asp-mobile-card text-center">
            <p class="text-xs text-slate-500">Live</p>
            <p class="text-2xl font-bold">{{ $stats['total'] }}</p>
        </div>
        <div class="asp-mobile-card text-center">
            <p class="text-xs text-slate-500">Queued</p>
            <p class="text-2xl font-bold">{{ $stats['open'] }}</p>
        </div>
        <div class="asp-mobile-card text-center">
            <p class="text-xs text-slate-500">Washing</p>
            <p class="text-2xl font-bold">{{ $stats['in_progress'] }}</p>
        </div>
        <div class="asp-mobile-card text-center">
            <p class="text-xs text-slate-500">Unassigned</p>
            <p class="text-2xl font-bold">{{ $stats['unassigned'] }}</p>
        </div>
    </div>

    <div
        x-data="liveJobBoard({
            jobCards: @js($jobCardsJson),
            canManage: @js($canManage),
        })"
    >
        <template x-if="jobCards.length === 0">
            <x-ui.empty-state title="No live jobs" description="Queued and in-progress vehicles appear here." />
        </template>

        <div class="asp-mobile-list md:grid md:grid-cols-2 md:gap-3 md:space-y-0">
            <template x-for="jobCard in jobCards" :key="jobCard.id">
                <div class="asp-mobile-card">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="font-mono text-[10px] uppercase text-slate-400" x-text="`Job #${jobCard.id}`"></p>
                            <h2 class="text-lg font-bold text-slate-900 dark:text-white" x-text="jobCard.registration_number"></h2>
                            <p class="text-sm text-slate-500" x-text="jobCard.customer_name"></p>
                            <p class="text-xs font-medium text-brand-primary-dim dark:text-brand-primary" x-text="jobCard.services_summary"></p>
                        </div>
                        <span class="rounded-full bg-sky-100 px-2 py-0.5 text-[10px] font-semibold uppercase text-sky-800 dark:bg-sky-900 dark:text-sky-200" x-text="labelFor(jobCard.status)"></span>
                    </div>

                    <div class="mt-3 flex gap-2" x-show="canManage">
                        <template x-for="status in ['open', 'in_progress', 'completed']" :key="status">
                            <button
                                type="button"
                                class="asp-mobile-chip flex-1 justify-center text-xs"
                                x-bind:disabled="jobCard.status === status || isUpdating(jobCard.id)"
                                @click="updateStatus(jobCard.id, status)"
                                x-text="status === 'open' ? 'Queue' : (status === 'in_progress' ? 'Wash' : 'Done')"
                            ></button>
                        </template>
                    </div>

                    <a :href="jobCard.view_url" class="mt-3 inline-flex text-sm font-semibold text-brand-primary">View details</a>
                </div>
            </template>
        </div>
    </div>
</x-layouts.mobile>
