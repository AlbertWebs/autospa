<x-ui.form-page
    eyebrow="Settings"
    title="Add Payment Method"
    subtitle="Configure a new accepted payment method."
    panel-title="Payment Method Details"
    panel-icon="payments"
    :action="route('settings.payment-methods.store')"
    submit-label="Create Payment Method"
    :cancel-url="route('settings.payment-methods.index')"
>
    @include('settings.payment-methods._form')
</x-ui.form-page>
