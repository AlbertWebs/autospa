@props([
    'name' => null,
    'ajax' => false,
])

@php
    $hasError = $name && $errors->has($name);
    $classes = 'asp-input' . ($hasError ? ' asp-input--error' : '');
@endphp

<input
    {{ $attributes->merge(['class' => $classes]) }}
    @if ($name && $ajax)
        :class="{ 'asp-input--error': errors['{{ $name }}'] }"
    @endif
/>
