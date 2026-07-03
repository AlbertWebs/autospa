@props(['title' => 'No data yet', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 py-16 text-center dark:border-slate-700 dark:bg-slate-900/50']) }}>
    <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-200 dark:bg-slate-800">
        <svg class="h-7 w-7 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
    </div>
    <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $title }}</h3>
    @if ($description)
        <p class="mt-2 max-w-sm text-sm text-slate-500 dark:text-slate-400">{{ $description }}</p>
    @endif
    @if (isset($action))
        <div class="mt-6">{{ $action }}</div>
    @endif
</div>
