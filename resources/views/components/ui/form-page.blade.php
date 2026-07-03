@props([
    'eyebrow' => null,
    'title',
    'subtitle' => null,
    'headerMobile' => null,
    'panelTitle' => null,
    'panelSubtitle' => 'All required fields are marked with an asterisk.',
    'panelIcon' => null,
    'action',
    'method' => 'POST',
    'submitLabel' => 'Save',
    'cancelUrl' => null,
    'maxWidth' => 'max-w-3xl',
    'showPanelHeader' => true,
])

<x-layouts.app>
    <x-slot name="header">
        <span class="hidden sm:inline">{{ $headerMobile ?? $title }}</span>
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
    </header>

    <div class="{{ $maxWidth }}">
        <div class="asp-panel">
            @if ($showPanelHeader && ($panelTitle || $panelIcon))
                <div class="asp-panel-header">
                    <div>
                        @if ($panelTitle)
                            <h2 class="asp-panel-title">{{ $panelTitle }}</h2>
                        @endif
                        @if ($panelSubtitle)
                            <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">{{ $panelSubtitle }}</p>
                        @endif
                    </div>
                    @if ($panelIcon)
                        <span class="material-symbols-outlined text-brand-primary-dim dark:text-brand-primary">{{ $panelIcon }}</span>
                    @endif
                </div>
            @endif

            <div class="asp-panel-body">
                <form
                    method="{{ in_array(strtoupper($method), ['GET']) ? 'GET' : 'POST' }}"
                    action="{{ $action }}"
                    class="asp-form"
                    {{ $attributes }}
                >
                    @csrf
                    @if (! in_array(strtoupper($method), ['GET', 'POST']))
                        @method($method)
                    @endif

                    {{ $slot }}

                    <x-ui.form-actions>
                        <button type="submit" class="asp-btn asp-btn-primary min-w-[10rem]">
                            <span class="material-symbols-outlined text-lg">check_circle</span>
                            {{ $submitLabel }}
                        </button>
                        @if ($cancelUrl)
                            <a href="{{ $cancelUrl }}" class="asp-btn asp-btn-ghost">Cancel</a>
                        @endif
                    </x-ui.form-actions>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
