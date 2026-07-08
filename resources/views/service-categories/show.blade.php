<x-layouts.app>
    <x-slot name="header"><span class="hidden sm:inline">Services</span></x-slot>

    <x-ui.section-header eyebrow="Services" />

    <div class="mb-6">
        @include('partials.crud.show-actions', [
            'backRoute' => route('services.categories.index'),
            'editRoute' => route('services.categories.edit', ['category' => $category]),
            'deleteRoute' => route('services.categories.destroy', ['category' => $category]),
            'deleteConfirm' => 'Delete this category?',
        ])
    </div>

    <x-ui.card>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Description</dt><dd>{{ $category->description ?? 'N/A' }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Sort Order</dt><dd>{{ $category->sort_order }}</dd></div>
            <div class="flex justify-between gap-4"><dt class="text-slate-500">Status</dt><dd>@if($category->is_active)<x-ui.badge color="green">Active</x-ui.badge>@else<x-ui.badge color="slate">Inactive</x-ui.badge>@endif</dd></div>
        </dl>
    </x-ui.card>
</x-layouts.app>
