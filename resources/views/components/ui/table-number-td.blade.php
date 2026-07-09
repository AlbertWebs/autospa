@props([
    'loop',
    'paginator' => null,
    'offset' => 0,
])

@php
    use App\Support\TableRowNumber;

    $number = TableRowNumber::for($loop, $paginator, $offset);
@endphp

<x-ui.td muted mono class="w-14 shrink-0 text-center">{{ $number }}</x-ui.td>
