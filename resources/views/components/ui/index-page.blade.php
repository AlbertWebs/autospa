@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'createRoute' => null,
    'createLabel' => 'Add New',
    'createIcon' => 'add',
])

<x-layouts.app>
    <x-slot name="header">
        <span class="hidden sm:inline">{{ $title }}</span>
    </x-slot>

    <header class="asp-page-header">
        <div>
            @if ($eyebrow)
                <p class="asp-page-eyebrow">{{ $eyebrow }}</p>
            @endif
            <h1 class="asp-page-title">{{ $title }}</h1>
            @if ($subtitle)
                <p class="asp-page-subtitle">{{ $subtitle }}</p>
            @endif
        </div>

        <div class="flex shrink-0 flex-wrap items-center gap-3">
            @isset($actions)
                {{ $actions }}
            @endisset

            @if ($createRoute)
                <a href="{{ $createRoute }}" class="asp-btn asp-btn-primary">
                    <span class="material-symbols-outlined text-lg">{{ $createIcon }}</span>
                    {{ $createLabel }}
                </a>
            @endif
        </div>
    </header>

    {{ $slot }}
</x-layouts.app>
