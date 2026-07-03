<x-ui.form-page
    eyebrow="Marketing"
    title="Edit SMS Campaign"
    subtitle="Update SMS message content and recipient settings."
    panel-title="Campaign Details"
    panel-icon="sms"
    :action="route('marketing.sms.update', $campaign)"
    method="PUT"
    submit-label="Save Changes"
    :cancel-url="route('marketing.sms.index')"
>
    @include('marketing.sms-campaigns._form', ['campaign' => $campaign])
</x-ui.form-page>
