@props(['title' => null, 'actionHref' => null, 'actionLabel' => null, 'noPadding' => false])

<div {{ $attributes->merge(['class' => 'asp-panel']) }}>
    @if ($title || isset($action) || $actionHref)
        <div class="asp-panel-header">
            @if ($title)
                <h2 class="asp-panel-title">{{ $title }}</h2>
            @endif
            @if ($actionHref)
                <a href="{{ $actionHref }}" class="text-xs font-medium text-brand-primary-dim hover:underline dark:text-brand-primary">{{ $actionLabel ?? 'View all' }}</a>
            @elseif (isset($action))
                <div>{{ $action }}</div>
            @endif
        </div>
    @endif
    <div @class(['asp-panel-body' => $slot->isNotEmpty() && ! $noPadding])>
        {{ $slot }}
    </div>
</div>
