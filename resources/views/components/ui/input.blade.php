@props([
    'name' => null,
    'ajax' => false,
])

@php
    $hasError = $name && $errors->has($name);
    $classes = 'asp-input' . ($hasError ? ' asp-input--error' : '');
@endphp

<input
    @if ($name) name="{{ $name }}" @endif
    {{ $attributes->merge(['class' => $classes]) }}
    @if ($name && $ajax)
        :class="{ 'asp-input--error': errors['{{ $name }}'] }"
    @endif
/>
