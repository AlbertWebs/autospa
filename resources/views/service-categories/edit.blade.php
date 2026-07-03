<x-ui.form-page
    eyebrow="Services"
    title="Edit Category"
    subtitle="Update category name and display settings."
    panel-title="Category Details"
    panel-icon="category"
    :action="route('services.categories.update', $category)"
    method="PUT"
    submit-label="Save Changes"
    :cancel-url="route('services.categories.index')"
>
    @include('service-categories._form', ['category' => $category])
</x-ui.form-page>
