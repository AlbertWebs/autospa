@props([
    'view' => null,
    'edit' => null,
    'viewVisible' => true,
    'editVisible' => true,
    'viewLabel' => 'View',
    'editLabel' => 'Edit',
])

@php
    $routeAccess = app(\App\Support\RouteAccess::class);
    $canViewRoute = $view ? $routeAccess->allowsUrl(auth()->user(), $view) : false;
    $canEditRoute = $edit ? $routeAccess->allowsUrl(auth()->user(), $edit) : false;
@endphp

<div {{ $attributes->merge(['class' => 'asp-table-actions']) }}>
    @if ($view && $viewVisible && $canViewRoute)
        <a href="{{ $view }}" class="asp-table-action asp-table-action--primary">
            <span class="material-symbols-outlined text-base">visibility</span>
            {{ $viewLabel }}
        </a>
    @endif

    @if ($edit && $editVisible && $canEditRoute)
        <a href="{{ $edit }}" class="asp-table-action">
            <span class="material-symbols-outlined text-base">edit</span>
            {{ $editLabel }}
        </a>
    @endif

    {{ $slot }}
</div>
