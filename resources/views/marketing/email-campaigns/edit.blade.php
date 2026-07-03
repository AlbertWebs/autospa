<x-ui.form-page
    eyebrow="Marketing"
    title="Edit Email Campaign"
    subtitle="Update email subject, body, and recipient settings."
    panel-title="Campaign Details"
    panel-icon="mail"
    :action="route('marketing.email.update', $campaign)"
    method="PUT"
    submit-label="Save Changes"
    :cancel-url="route('marketing.email.index')"
>
    @include('marketing.email-campaigns._form', ['campaign' => $campaign])
</x-ui.form-page>
