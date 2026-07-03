@props(['backRoute' => null, 'editRoute' => null, 'deleteRoute' => null, 'deleteConfirm' => 'Are you sure you want to delete this item?'])

<div class="flex flex-wrap items-center gap-2">
    @if ($backRoute)
        <a href="{{ $backRoute }}" class="inline-flex items-center gap-2 rounded-xl bg-slate-100 px-4 py-2 text-sm font-medium text-slate-700 transition hover:bg-slate-200 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back
        </a>
    @endif
    @if ($editRoute)
        <a href="{{ $editRoute }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700">Edit</a>
    @endif
    @if ($deleteRoute)
        <form method="POST" action="{{ $deleteRoute }}" onsubmit="return confirm(@json($deleteConfirm))">
            @csrf
            @method('DELETE')
            <x-danger-button type="submit">Delete</x-danger-button>
        </form>
    @endif
    {{ $slot ?? '' }}
</div>
