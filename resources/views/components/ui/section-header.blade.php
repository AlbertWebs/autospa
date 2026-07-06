@props([
    'eyebrow',
])

<header {{ $attributes->merge(['class' => 'asp-page-header']) }}>
    <div>
        <p class="asp-page-eyebrow">{{ $eyebrow }}</p>
    </div>

    @if (! $slot->isEmpty())
        <div class="flex shrink-0 flex-wrap items-center gap-3">
            {{ $slot }}
        </div>
    @endif
</header>
