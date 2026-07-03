@props(['padding' => true])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900' . ($padding ? ' p-6' : '')]) }}>
    {{ $slot }}
</div>
