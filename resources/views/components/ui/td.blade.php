@props([
    'align' => 'left',
    'primary' => false,
    'muted' => false,
    'mono' => false,
    'wrap' => false,
])

@php
    $classes = collect([
        'asp-td',
        $align === 'right' ? 'asp-td--right' : '',
        $primary ? 'asp-td--primary' : '',
        $muted ? 'asp-td--muted' : '',
        $mono ? 'asp-td--mono' : '',
        $wrap ? 'asp-td--wrap' : '',
    ])->filter()->implode(' ');
@endphp

<td {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</td>
