@props([
    'size' => 'md',
    'alt' => 'AutoSpa',
])

@php
    $sizeClass = match ($size) {
        'sm' => 'h-8 w-8',
        'lg' => 'h-12 w-12',
        default => 'h-10 w-10',
    };
@endphp

<img
    src="{{ asset('logo.png') }}"
    alt="{{ $alt }}"
    {{ $attributes->merge(['class' => "shrink-0 rounded-xl object-contain {$sizeClass}"]) }}
>
