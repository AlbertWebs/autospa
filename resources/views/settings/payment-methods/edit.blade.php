<x-ui.form-page
    eyebrow="Settings"
    title="Edit Payment Method"
    subtitle="Update payment method name and configuration."
    panel-title="Payment Method Details"
    panel-icon="payments"
    :action="route('settings.payment-methods.update', $paymentMethod)"
    method="PUT"
    submit-label="Save Changes"
    :cancel-url="route('settings.payment-methods.index')"
>
    @include('settings.payment-methods._form', ['paymentMethod' => $paymentMethod])
</x-ui.form-page>
