<x-ui.index-page
    eyebrow="Staff"
    title="Commissions"
    subtitle="Track staff commission earnings by period."
>
    <x-ui.data-table
        :paginator="$commissions"
        :empty="$commissions->isEmpty()"
        empty-title="No commissions"
        empty-description="Staff commission records will appear here."
    >
        <x-slot name="header">
            <x-ui.th>Employee</x-ui.th>
            <x-ui.th>Amount</x-ui.th>
            <x-ui.th>Period</x-ui.th>
            <x-ui.th>Status</x-ui.th>
        </x-slot>

        @foreach ($commissions as $commission)
            <tr class="asp-table-row">
                <x-ui.td primary>{{ $commission->employee?->full_name ?? '—' }}</x-ui.td>
                <x-ui.td>{{ number_format($commission->amount ?? 0, 2) }}</x-ui.td>
                <x-ui.td muted>{{ $commission->period ?? '—' }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge color="indigo">{{ $commission->status ?? 'pending' }}</x-ui.badge>
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
