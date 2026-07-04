<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $campaign->name }}</h1></x-slot>

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('marketing.email.index'),
            'editRoute' => route('marketing.email.edit', $campaign),
            'deleteRoute' => route('marketing.email.destroy', $campaign),
            'deleteConfirm' => 'Delete this campaign?',
        ])
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Subject</dt><dd class="font-medium">{{ $campaign->subject }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd><x-ui.badge color="indigo">{{ ucfirst($campaign->status) }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Scheduled</dt><dd>{{ $campaign->scheduled_at?->format('M j, Y g:i A') ?? 'N/A' }}</dd></div>
            <div><dt class="mb-1 text-slate-500">Body</dt><dd class="prose prose-sm max-w-none rounded-lg bg-slate-50 p-4 dark:bg-slate-800">{!! nl2br(e($campaign->body)) !!}</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
