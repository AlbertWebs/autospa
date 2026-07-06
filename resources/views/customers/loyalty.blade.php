<x-ui.index-page
    eyebrow="Customers"
    title="Loyalty Program"
    :subtitle="$loyaltyEnabled ? $loyaltySummary : 'Loyalty program is currently disabled. Enable it under Settings → Company.'"
>
    <x-ui.data-table
        :paginator="$transactions"
        :empty="$transactions->isEmpty()"
        empty-title="No loyalty transactions"
        empty-description="Customer loyalty activity will appear here."
    >
        <x-slot name="header">
            <x-ui.th>Customer</x-ui.th>
            <x-ui.th>Type</x-ui.th>
            <x-ui.th>Points</x-ui.th>
            <x-ui.th>Date</x-ui.th>
        </x-slot>

        @foreach ($transactions as $transaction)
            <tr class="asp-table-row">
                <x-ui.td primary>{{ $transaction->customer?->full_name ?? 'N/A' }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge color="indigo">{{ $transaction->type }}</x-ui.badge>
                </x-ui.td>
                <x-ui.td>{{ $transaction->points }}</x-ui.td>
                <x-ui.td muted>{{ $transaction->created_at?->format('M j, Y') }}</x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
