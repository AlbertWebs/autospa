<x-ui.form-page
    eyebrow="Marketing"
    title="New SMS Campaign"
    subtitle="Create a bulk SMS message for your customers."
    panel-title="Campaign Details"
    panel-icon="sms"
    :action="route('marketing.sms.store')"
    submit-label="Create Campaign"
    :cancel-url="route('marketing.sms.index')"
>
    @include('marketing.sms-campaigns._form')
</x-ui.form-page>
