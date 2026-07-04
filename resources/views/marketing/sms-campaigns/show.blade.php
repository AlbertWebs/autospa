<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $campaign->name }}</h1></x-slot>

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('marketing.sms.index'),
            'editRoute' => route('marketing.sms.edit', $campaign),
            'deleteRoute' => route('marketing.sms.destroy', $campaign),
            'deleteConfirm' => 'Delete this campaign?',
        ])
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-ui.badge color="indigo">{{ ucfirst($campaign->status) }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Scheduled</dt><dd>{{ $campaign->scheduled_at?->format('M j, Y g:i A') ?? 'N/A' }}</dd></div>
            <div><dt class="mb-1 text-slate-500">Message</dt><dd class="rounded-lg bg-slate-50 p-3 dark:bg-slate-800">{{ $campaign->message }}</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
