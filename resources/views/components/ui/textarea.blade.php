@props([
    'name' => null,
    'ajax' => false,
])

@php
    $hasError = $name && $errors->has($name);
    $classes = 'asp-textarea' . ($hasError ? ' asp-textarea--error' : '');
@endphp

<textarea
    {{ $attributes->merge(['class' => $classes]) }}
    @if ($name && $ajax)
        :class="{ 'asp-textarea--error': errors['{{ $name }}'] }"
    @endif
>{{ $slot }}</textarea>
