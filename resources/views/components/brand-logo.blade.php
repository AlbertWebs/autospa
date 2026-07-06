@props([
    'size' => 'md',
    'alt' => 'AutoSpa',
    'variant' => 'app',
])

@php
    [$classes, $width, $height] = match (true) {
        $variant === 'auth' => ['auth-brand-logo', 48, 48],
        $size === 'sm' => ['shrink-0 rounded-xl object-contain h-10 w-10 ring-2 ring-white', 40, 40],
        $size === 'lg' => ['shrink-0 rounded-xl object-contain h-12 w-12 ring-2 ring-white', 48, 48],
        default => ['shrink-0 rounded-xl object-contain h-10 w-10 ring-2 ring-white', 40, 40],
    };
@endphp

<img
    src="{{ asset('logo.png') }}"
    alt="{{ $alt }}"
    width="{{ $width }}"
    height="{{ $height }}"
    {{ $attributes->merge(['class' => $classes]) }}
>
