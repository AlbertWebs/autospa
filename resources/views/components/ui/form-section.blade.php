@props([
    'title' => null,
    'description' => null,
])

<section {{ $attributes->merge(['class' => 'asp-form-section']) }}>
    @if ($title || $description)
        <header class="asp-form-section-header">
            @if ($title)
                <h2 class="asp-form-section-title">{{ $title }}</h2>
            @endif
            @if ($description)
                <p class="asp-form-section-desc">{{ $description }}</p>
            @endif
        </header>
    @endif
    {{ $slot }}
</section>
