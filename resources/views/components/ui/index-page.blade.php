@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'createRoute' => null,
    'createVisible' => true,
    'createLabel' => 'Add New',
    'createIcon' => 'add',
])

@php
    $routeAccess = app(\App\Support\RouteAccess::class);
    $canUseCreateRoute = $createRoute
        ? $routeAccess->allowsUrl(auth()->user(), $createRoute)
        : false;
@endphp

<x-layouts.app>
    <x-slot name="header">
        <span class="hidden sm:inline">{{ $eyebrow ?? $title }}</span>
    </x-slot>

    <x-ui.section-header :eyebrow="$eyebrow ?? $title">
        @isset($actions)
            {{ $actions }}
        @endisset

        @if ($createRoute && $createVisible && $canUseCreateRoute)
            <a href="{{ $createRoute }}" class="asp-btn asp-btn-primary">
                <span class="material-symbols-outlined text-lg">{{ $createIcon }}</span>
                {{ $createLabel }}
            </a>
        @endif
    </x-ui.section-header>

    {{ $slot }}
</x-layouts.app>
