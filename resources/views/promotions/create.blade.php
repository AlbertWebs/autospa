<x-ui.form-page
    eyebrow="Marketing"
    title="Add Promotion"
    subtitle="Create a new promotional offer or discount."
    panel-title="Promotion Details"
    panel-icon="sell"
    :action="route('promotions.store')"
    submit-label="Create Promotion"
    :cancel-url="route('promotions.index')"
>
    @include('promotions._form')
</x-ui.form-page>
