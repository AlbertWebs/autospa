@props([
    'variant' => 'default',
    'label',
    'value',
    'icon' => 'analytics',
    'hint' => null,
    'href' => null,
])

@php
    $url = null;

    if ($href) {
        $routeAccess = app(\App\Support\RouteAccess::class);
        $url = $routeAccess->allowsUrl(auth()->user(), $href) ? $href : null;
    }

    $tag = $url ? 'a' : 'div';
    $classes = collect([
        'asp-stat',
        'asp-stat--' . $variant,
        $url ? 'asp-stat--link' : null,
    ])->filter()->implode(' ');
@endphp

<{{ $tag }}
    @if ($url) href="{{ $url }}" @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full bg-current opacity-[0.04] blur-2xl"></div>
    <div class="relative flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="asp-stat-label">{{ $label }}</p>
            <p class="asp-stat-value">{{ $value }}</p>
            @if ($hint)
                <p class="mt-1 text-xs text-slate-500 dark:text-slate-500">{{ $hint }}</p>
            @endif
        </div>
        <div class="asp-stat-icon">
            <span class="material-symbols-outlined text-[22px]">{{ $icon }}</span>
        </div>
    </div>
</{{ $tag }}>
