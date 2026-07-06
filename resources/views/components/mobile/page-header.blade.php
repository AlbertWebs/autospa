@props(['title', 'subtitle' => null, 'back' => null, 'actionHref' => null, 'actionLabel' => null])

<header class="asp-mobile-page-header">
    <div class="flex items-start gap-3">
        @if ($back)
            <a href="{{ $back }}" class="asp-mobile-back-btn" aria-label="Go back">
                <span class="material-symbols-outlined text-[22px]">arrow_back</span>
            </a>
        @endif

        <div class="min-w-0 flex-1">
            <h1 class="text-xl font-bold text-slate-900 dark:text-white">{{ $title }}</h1>
            @if ($subtitle)
                <p class="mt-0.5 text-sm text-slate-500 dark:text-slate-400">{{ $subtitle }}</p>
            @endif
        </div>

        @if ($actionHref && $actionLabel)
            <a href="{{ $actionHref }}" class="asp-mobile-action-btn">{{ $actionLabel }}</a>
        @endif
    </div>
</header>
