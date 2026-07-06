@props(['href', 'title', 'subtitle' => null, 'meta' => null, 'status' => null, 'statusClass' => ''])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'asp-mobile-list-card']) }}>
    <div class="min-w-0 flex-1">
        <p class="truncate font-semibold text-slate-900 dark:text-white">{{ $title }}</p>
        @if ($subtitle)
            <p class="mt-0.5 truncate text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
        @endif
        @if ($meta)
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ $meta }}</p>
        @endif
    </div>

    <div class="flex shrink-0 items-center gap-2">
        @if ($status)
            <span @class(['asp-mobile-status', $statusClass])>{{ $status }}</span>
        @endif
        <span class="material-symbols-outlined text-slate-400">chevron_right</span>
    </div>
</a>
