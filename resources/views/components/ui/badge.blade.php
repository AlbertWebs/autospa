@props(['color' => 'slate'])

<span {{ $attributes->merge(['class' => "inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium bg-{$color}-100 text-{$color}-800 dark:bg-{$color}-900 dark:text-{$color}-200"]) }}>
    {{ $slot }}
</span>
