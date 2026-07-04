<x-ui.index-page
    eyebrow="Marketing"
    title="SMS Campaigns"
    subtitle="Create and schedule SMS campaigns to reach customers."
    :create-route="route('marketing.sms.create')"
    create-label="New SMS Campaign"
>
    <x-ui.data-table
        :paginator="$campaigns"
        :empty="$campaigns->isEmpty()"
        empty-title="No SMS campaigns"
        empty-description="Create SMS campaigns to reach customers."
    >
        <x-slot name="header">
            <x-ui.th>Name</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th>Scheduled</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($campaigns as $campaign)
            <tr class="asp-table-row">
                <x-ui.td primary>{{ $campaign->name }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge color="indigo">{{ ucfirst($campaign->status) }}</x-ui.badge>
                </x-ui.td>
                <x-ui.td muted>{{ $campaign->scheduled_at?->format('M j, Y g:i A') ?? 'N/A' }}</x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions
                        :view="route('marketing.sms.show', $campaign)"
                        :edit="route('marketing.sms.edit', $campaign)"
                    />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
