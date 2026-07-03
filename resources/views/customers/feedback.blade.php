<x-ui.index-page
    eyebrow="Customers"
    title="Customer Feedback"
    subtitle="Review ratings and comments from your customers."
>
    <x-ui.data-table
        :paginator="$notes"
        :empty="$notes->isEmpty()"
        empty-title="No feedback yet"
        empty-description="Customer feedback will appear here."
    >
        <x-slot name="header">
            <x-ui.th>Customer</x-ui.th>
            <x-ui.th>Rating</x-ui.th>
            <x-ui.th>Feedback</x-ui.th>
            <x-ui.th>Date</x-ui.th>
        </x-slot>

        @foreach ($notes as $note)
            <tr class="asp-table-row">
                <x-ui.td primary>{{ $note->customer?->full_name ?? '—' }}</x-ui.td>
                <x-ui.td>{{ $note->rating ?? '—' }}</x-ui.td>
                <x-ui.td muted>{{ Str::limit($note->content ?? $note->notes ?? '', 80) }}</x-ui.td>
                <x-ui.td muted>{{ $note->created_at?->format('M j, Y') }}</x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
