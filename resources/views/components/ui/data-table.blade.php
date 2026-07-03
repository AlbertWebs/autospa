@props([
    'paginator' => null,
    'empty' => false,
    'emptyTitle' => 'No records yet',
    'emptyDescription' => null,
    'count' => null,
])

@php
    $total = $count ?? ($paginator?->total());
@endphp

<div {{ $attributes->merge(['class' => 'asp-panel asp-table-panel']) }}>
    <div class="asp-table-wrap">
        <table class="asp-table">
            <thead class="asp-table-head">
                <tr>{{ $header }}</tr>
            </thead>
            <tbody class="asp-table-body">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    @if ($empty)
        <x-ui.empty-state :title="$emptyTitle" :description="$emptyDescription" />
    @endif

    @if ($paginator && ($paginator->hasPages() || $total))
        <div class="asp-table-footer">
            @if ($total !== null)
                <p class="asp-table-count">
                    {{ $paginator ? $paginator->firstItem() . '–' . $paginator->lastItem() . ' of ' . number_format($total) : number_format($total) . ' records' }}
                </p>
            @else
                <span></span>
            @endif

            @if ($paginator && $paginator->hasPages())
                <div class="asp-table-pagination">
                    {{ $paginator->links() }}
                </div>
            @endif
        </div>
    @endif
</div>
