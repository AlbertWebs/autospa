<x-ui.index-page
    eyebrow="Marketing"
    title="Email Campaigns"
    subtitle="Create and send email campaigns to engage customers."
    :create-route="route('marketing.email.create')"
    create-label="New Email Campaign"
>
    <x-ui.data-table
        :paginator="$campaigns"
        :empty="$campaigns->isEmpty()"
        empty-title="No email campaigns"
        empty-description="Create email campaigns to engage customers."
    >
        <x-slot name="header">
            <x-ui.th>Name</x-ui.th>
            <x-ui.th>Subject</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($campaigns as $campaign)
            <tr class="asp-table-row">
                <x-ui.td primary>{{ $campaign->name }}</x-ui.td>
                <x-ui.td muted>{{ Str::limit($campaign->subject, 40) }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge color="indigo">{{ ucfirst($campaign->status) }}</x-ui.badge>
                </x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions
                        :view="route('marketing.email.show', $campaign)"
                        :edit="route('marketing.email.edit', $campaign)"
                    />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
