@props([
    'name',
    'value',
    'checked' => false,
])

<label {{ $attributes->merge(['class' => 'asp-checkbox-card']) }}>
    <input
        type="checkbox"
        name="{{ $name }}"
        value="{{ $value }}"
        class="asp-checkbox"
        @checked($checked)
    />
    <span class="text-sm text-slate-700 dark:text-slate-300">{{ $slot }}</span>
</label>
