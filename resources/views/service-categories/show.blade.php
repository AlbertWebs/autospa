<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ $category->name }}</h1></x-slot>

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('services.categories.index'),
            'editRoute' => route('services.categories.edit', $category),
            'deleteRoute' => route('services.categories.destroy', $category),
            'deleteConfirm' => 'Delete this category?',
        ])
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Description</dt><dd>{{ $category->description ?? '—' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Sort Order</dt><dd>{{ $category->sort_order }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($category->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
