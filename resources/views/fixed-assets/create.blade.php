<x-ui.form-page
    eyebrow="Inventory"
    title="Add Fixed Asset"
    subtitle="Record a company-owned asset for tracking and valuation."
    panel-title="Asset Details"
    panel-icon="account_balance"
    :action="route('fixed-assets.store')"
    submit-label="Record Asset"
    :cancel-url="route('fixed-assets.index')"
>
    @include('fixed-assets._form')
</x-ui.form-page>
