@props([
    'view' => null,
    'edit' => null,
    'delete' => null,
    'viewVisible' => true,
    'editVisible' => true,
    'deleteVisible' => true,
    'viewLabel' => 'View',
    'editLabel' => 'Edit',
    'deleteLabel' => 'Delete',
    'deleteConfirm' => 'Are you sure you want to delete this item?',
])

@php
    $routeAccess = app(\App\Support\RouteAccess::class);
    $canViewRoute = $view ? $routeAccess->allowsUrl(auth()->user(), $view) : false;
    $canEditRoute = $edit ? $routeAccess->allowsUrl(auth()->user(), $edit) : false;
    $canDeleteRoute = $delete ? $routeAccess->allowsUrl(auth()->user(), $delete, 'DELETE') : false;
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

    @if ($delete && $deleteVisible && $canDeleteRoute)
        <form method="POST" action="{{ $delete }}" class="inline" onsubmit="return confirm(@json($deleteConfirm))">
            @csrf
            @method('DELETE')
            <button type="submit" class="asp-table-action text-rose-600 hover:text-rose-700 dark:text-rose-400 dark:hover:text-rose-300">
                <span class="material-symbols-outlined text-base">delete</span>
                {{ $deleteLabel }}
            </button>
        </form>
    @endif

    {{ $slot }}
</div>
