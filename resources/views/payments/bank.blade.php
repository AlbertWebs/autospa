<x-ui.index-page
    eyebrow="Finance"
    title="Bank Transfer Payments"
    subtitle="Payment records received via bank transfer."
>
    <x-ui.data-table
        :paginator="$payments"
        :empty="$payments->isEmpty()"
        empty-title="No payments"
        empty-description="Payment records will appear here."
    >
        <x-slot name="header">
            <x-ui.th>Payment #</x-ui.th>
            <x-ui.th>Customer</x-ui.th>
            <x-ui.th>Amount</x-ui.th>
            <x-ui.th>Method</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($payments as $payment)
            <tr class="asp-table-row">
                <x-ui.td primary mono>#{{ $payment->id }}</x-ui.td>
                <x-ui.td>{{ $payment->customer?->full_name ?? 'N/A' }}</x-ui.td>
                <x-ui.td>{{ number_format($payment->amount ?? 0, 2) }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge color="indigo">{{ $payment->method ?? $payment->payment_method?->name ?? 'N/A' }}</x-ui.badge>
                </x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions :view="route('payments.show', $payment)" />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
