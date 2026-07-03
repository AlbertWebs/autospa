@props(['paginator' => null])

@if ($paginator && $paginator->hasPages())
    <div class="mt-6 border-t border-slate-200 pt-4 dark:border-slate-800">
        {{ $paginator->links() }}
    </div>
@endif
