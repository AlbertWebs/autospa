@props([
    'view' => null,
    'edit' => null,
    'viewLabel' => 'View',
    'editLabel' => 'Edit',
])

<div {{ $attributes->merge(['class' => 'asp-table-actions']) }}>
    @if ($view)
        <a href="{{ $view }}" class="asp-table-action asp-table-action--primary">
            <span class="material-symbols-outlined text-base">visibility</span>
            {{ $viewLabel }}
        </a>
    @endif

    @if ($edit)
        <a href="{{ $edit }}" class="asp-table-action">
            <span class="material-symbols-outlined text-base">edit</span>
            {{ $editLabel }}
        </a>
    @endif

    {{ $slot }}
</div>
