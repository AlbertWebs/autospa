<x-ui.form-page
    eyebrow="Marketing"
    title="Edit Promotion"
    subtitle="Update promotion terms, dates, and discount rules."
    panel-title="Promotion Details"
    panel-icon="sell"
    :action="route('promotions.update', $promotion)"
    method="PUT"
    submit-label="Save Changes"
    :cancel-url="route('promotions.index')"
>
    @include('promotions._form', ['promotion' => $promotion])
</x-ui.form-page>
