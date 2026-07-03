@props([
    'align' => 'left',
])

@php
    $alignClass = match ($align) {
        'right' => 'asp-th--right',
        'center' => 'asp-th--center',
        default => '',
    };
@endphp

<th {{ $attributes->merge(['class' => trim("asp-th {$alignClass}")]) }}>{{ $slot }}</th>
