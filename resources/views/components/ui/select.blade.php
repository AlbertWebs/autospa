@props([
    'name' => null,
    'ajax' => false,
])

@php
    $hasError = $name && $errors->has($name);
    $classes = 'asp-select' . ($hasError ? ' asp-select--error' : '');
@endphp

<select
    {{ $attributes->merge(['class' => $classes]) }}
    @if ($name && $ajax)
        :class="{ 'asp-select--error': errors['{{ $name }}'] }"
    @endif
>
    {{ $slot }}
</select>
