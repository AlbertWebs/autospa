<x-layouts.app>
    <x-slot name="header"><h1 class="text-2xl font-bold text-slate-900 dark:text-white">Customer Feedback</h1></x-slot>
    <x-ui.card :padding="false">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-800">
                <thead class="bg-slate-50 dark:bg-slate-800/50"><tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Customer</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Rating</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Feedback</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-slate-500">Date</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse ($notes as $note)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                            <td class="whitespace-nowrap px-6 py-4 font-medium">{{ $note->customer?->full_name ?? '—' }}</td>
                            <td class="whitespace-nowrap px-6 py-4">{{ $note->rating ?? '—' }}</td>
                            <td class="px-6 py-4 text-slate-500">{{ Str::limit($note->content ?? $note->notes ?? '', 80) }}</td>
                            <td class="whitespace-nowrap px-6 py-4 text-slate-500">{{ $note->created_at?->format('M j, Y') }}</td>
                        </tr>
                    @empty @endforelse
                </tbody>
            </table>
        </div>
        @if ($notes->isEmpty())<x-ui.empty-state title="No feedback yet" description="Customer feedback will appear here." />@endif
        @include('partials.crud.pagination', ['paginator' => $notes])
    </x-ui.card>
</x-layouts.app>
