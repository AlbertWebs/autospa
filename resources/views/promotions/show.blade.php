<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $promotion->name }}</h1></x-slot>

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('promotions.index'),
            'editRoute' => route('promotions.edit', $promotion),
            'deleteRoute' => route('promotions.destroy', $promotion),
            'deleteConfirm' => 'Delete this promotion?',
        ])
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Code</dt><dd><x-ui.badge color="indigo">{{ $promotion->code }}</x-ui.badge></dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Type / Value</dt><dd>{{ ucfirst($promotion->type) }} — {{ $promotion->type === 'percentage' ? $promotion->value.'%' : number_format($promotion->value, 2) }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Valid</dt><dd>{{ $promotion->starts_at?->format('M j, Y') ?? '—' }} — {{ $promotion->ends_at?->format('M j, Y') ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($promotion->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Description</dt><dd>{{ $promotion->description ?? '—' }}</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
