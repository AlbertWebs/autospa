@php
    $createRoute = $createRoute ?? null;
    $createLabel = $createLabel ?? 'Add New';
    $title = $title ?? null;
@endphp

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    @if ($title)
        <h1 class="text-2xl font-bold text-slate-900 dark:text-white sm:hidden">{{ $title }}</h1>
    @endif
    @if ($createRoute)
        <a href="{{ $createRoute }}" @class([
            'inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2',
            'ml-auto' => ! $title,
        ])>
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            {{ $createLabel }}
        </a>
    @endif
</div>
