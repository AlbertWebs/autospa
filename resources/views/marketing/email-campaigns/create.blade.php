<x-ui.form-page
    eyebrow="Marketing"
    title="New Email Campaign"
    subtitle="Create a bulk email message for your customers."
    panel-title="Campaign Details"
    panel-icon="mail"
    :action="route('marketing.email.store')"
    submit-label="Create Campaign"
    :cancel-url="route('marketing.email.index')"
>
    @include('marketing.email-campaigns._form')
</x-ui.form-page>
