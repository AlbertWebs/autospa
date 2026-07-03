<x-ui.form-page
    eyebrow="Staff"
    title="Record Attendance"
    subtitle="Log employee check-in or check-out for today."
    panel-title="Attendance Details"
    panel-icon="schedule"
    :action="route('attendance.store')"
    submit-label="Save Attendance"
    :cancel-url="route('attendance.index')"
>
    @include('attendance._form')
</x-ui.form-page>
