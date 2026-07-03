<x-ui.form-page
    eyebrow="Operations"
    title="Edit Booking"
    subtitle="Update booking date, time, and service details."
    panel-title="Booking Details"
    panel-icon="calendar_month"
    :action="route('bookings.update', $booking)"
    method="PUT"
    submit-label="Save Changes"
    :cancel-url="route('bookings.index')"
>
    @include('bookings._form', ['booking' => $booking])
</x-ui.form-page>
