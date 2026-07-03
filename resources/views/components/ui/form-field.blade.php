@props([
    'label' => null,
    'for' => null,
    'hint' => null,
    'required' => false,
    'name' => null,
    'colSpan' => null,
    'ajax' => false,
])

@php
    $colClass = match ((int) $colSpan) {
        2 => 'sm:col-span-2',
        default => '',
    };
@endphp

<div {{ $attributes->merge(['class' => trim("asp-form-field {$colClass}")]) }}>
    @if ($label)
        <label
            @if ($for) for="{{ $for }}" @endif
            @class(['asp-label', 'asp-label-required' => $required])
        >{{ $label }}</label>
    @endif

    {{ $slot }}

    @if ($hint)
        <p class="asp-field-hint">{{ $hint }}</p>
    @endif

    @if ($name && $ajax)
        <p class="asp-field-error" x-show="errors['{{ $name }}']" x-cloak>
            <span class="material-symbols-outlined text-sm">error</span>
            <span x-text="errors['{{ $name }}']?.[0]"></span>
        </p>
    @endif

    @if ($name)
        @error($name)
            <p class="asp-field-error">
                <span class="material-symbols-outlined text-sm">error</span>
                {{ $message }}
            </p>
        @enderror
    @endif
</div>
