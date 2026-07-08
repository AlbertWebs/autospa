@props(['label', 'value', 'icon' => 'analytics', 'variant' => 'default', 'href' => null, 'hint' => null])

@php
    $url = null;

    if ($href) {
        $routeAccess = app(\App\Support\RouteAccess::class);
        $url = $routeAccess->allowsUrl(auth()->user(), $href) ? $href : null;
    }

    $tag = $url ? 'a' : 'div';
    $classes = collect([
        'asp-mobile-stat',
        'asp-mobile-stat--' . $variant,
        $url ? 'asp-mobile-stat--link' : null,
    ])->filter()->implode(' ');
@endphp

<{{ $tag }}
    @if ($url) href="{{ $url }}" @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    <div class="flex items-start justify-between gap-2">
        <div class="min-w-0">
            <p class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $label }}</p>
            <p class="mt-1 text-lg font-bold text-slate-900 dark:text-white">{{ $value }}</p>
            @if ($hint)
                <p class="mt-0.5 text-[10px] leading-snug text-slate-500 dark:text-slate-400">{{ $hint }}</p>
            @endif
        </div>
        <span class="material-symbols-outlined text-[22px] text-brand-primary opacity-80">{{ $icon }}</span>
    </div>
</{{ $tag }}>
