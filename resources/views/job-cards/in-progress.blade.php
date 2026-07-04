<x-ui.index-page
    eyebrow="Operations"
    title="In Progress"
    subtitle="Jobs currently being worked on by your team."
    :create-route="route('job-cards.create')"
    create-label="New Job Card"
>
    <x-ui.data-table
        :paginator="$jobCards"
        :empty="$jobCards->isEmpty()"
        empty-title="No jobs in progress"
        empty-description="Jobs currently being worked on will appear here."
    >
        <x-slot name="header">
            <x-ui.th>#</x-ui.th>
            <x-ui.th>Customer</x-ui.th>
            <x-ui.th>Vehicle</x-ui.th>
            <x-ui.th>Status</x-ui.th>
            <x-ui.th align="right">Actions</x-ui.th>
        </x-slot>

        @foreach ($jobCards as $jobCard)
            <tr class="asp-table-row">
                <x-ui.td mono primary>#{{ $jobCard->id }}</x-ui.td>
                <x-ui.td>{{ $jobCard->customer?->full_name ?? 'N/A' }}</x-ui.td>
                <x-ui.td mono>{{ $jobCard->vehicle?->registration_number ?? 'N/A' }}</x-ui.td>
                <x-ui.td>
                    <x-ui.badge color="indigo">{{ $jobCard->status->label() }}</x-ui.badge>
                </x-ui.td>
                <x-ui.td align="right">
                    <x-ui.table-actions
                        :view="route('job-cards.show', $jobCard)"
                        :edit="route('job-cards.edit', $jobCard)"
                    />
                </x-ui.td>
            </tr>
        @endforeach
    </x-ui.data-table>
</x-ui.index-page>
