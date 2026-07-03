@props(['href', 'icon', 'label', 'description' => null])

<a href="{{ $href }}" {{ $attributes->merge(['class' => 'asp-action group']) }}>
    <span class="asp-action-icon">
        <span class="material-symbols-outlined text-[20px]">{{ $icon }}</span>
    </span>
    <span class="min-w-0">
        <span class="block text-sm font-semibold text-slate-800 dark:text-white">{{ $label }}</span>
        @if ($description)
            <span class="block text-xs text-slate-500 dark:text-slate-400">{{ $description }}</span>
        @endif
    </span>
    <span class="material-symbols-outlined ml-auto text-slate-400 opacity-0 transition group-hover:opacity-100 dark:text-slate-500">arrow_forward</span>
</a>
