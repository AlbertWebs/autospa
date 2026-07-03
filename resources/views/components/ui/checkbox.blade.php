@props([
    'name',
    'value' => '1',
    'checked' => false,
    'label' => null,
])

<label {{ $attributes->merge(['class' => 'asp-checkbox-label']) }}>
    <input
        type="checkbox"
        name="{{ $name }}"
        value="{{ $value }}"
        class="asp-checkbox"
        @checked($checked)
    />
    <span class="text-sm text-slate-700 dark:text-slate-300">{{ $label ?? $slot }}</span>
</label>
