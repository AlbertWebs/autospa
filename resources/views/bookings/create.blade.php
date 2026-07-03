<x-ui.form-page
    eyebrow="Operations"
    title="New Booking"
    subtitle="Schedule a new service appointment."
    panel-title="Booking Details"
    panel-icon="calendar_month"
    :action="route('bookings.store')"
    submit-label="Create Booking"
    :cancel-url="route('bookings.index')"
>
    @include('bookings._form')
</x-ui.form-page>
