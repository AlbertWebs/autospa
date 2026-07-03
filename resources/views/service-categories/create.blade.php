<x-ui.form-page
    eyebrow="Services"
    title="Add Category"
    subtitle="Create a new category to organize services."
    panel-title="Category Details"
    panel-icon="category"
    :action="route('services.categories.store')"
    submit-label="Create Category"
    :cancel-url="route('services.categories.index')"
>
    @include('service-categories._form')
</x-ui.form-page>
