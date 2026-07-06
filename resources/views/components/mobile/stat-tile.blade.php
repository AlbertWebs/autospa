@props(['label', 'value', 'icon' => 'analytics', 'variant' => 'default', 'href' => null])

@php
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'asp-mobile-stat asp-mobile-stat--' . $variant]) }}>
    <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
            <p class="mt-1 text-lg font-bold text-slate-900 dark:text-white">{{ $value }}</p>
        </div>
        <span class="material-symbols-outlined text-[22px] text-brand-primary opacity-80">{{ $icon }}</span>
    </div>
</{{ $tag }}>
