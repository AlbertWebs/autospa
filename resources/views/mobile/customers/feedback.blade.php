<x-layouts.mobile title="Feedback">
    <x-mobile.page-header title="Customer Feedback" :back="route('mobile.customers.index')" />

    <div class="asp-mobile-list">
        @forelse ($notes as $note)
            <div class="asp-mobile-card text-sm">
                <p class="font-semibold">{{ $note->customer?->full_name }}</p>
                <p class="mt-1 text-slate-700 dark:text-slate-300">{{ $note->note }}</p>
                <p class="mt-2 text-xs text-slate-500">{{ $note->user?->name }} · {{ $note->created_at->diffForHumans() }}</p>
            </div>
        @empty
            <x-ui.empty-state title="No feedback yet" />
        @endforelse
    </div>

    <div class="mt-4">{{ $notes->links() }}</div>
</x-layouts.mobile>
