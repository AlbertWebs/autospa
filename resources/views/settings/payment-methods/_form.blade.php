@php $paymentMethod = $paymentMethod ?? null; @endphp

<x-ui.form-section title="Payment Method" description="Name, slug, and availability for this payment method.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Name" for="name" name="name" :required="true">
            <x-ui.input id="name" name="name" :value="old('name', $paymentMethod->name ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Slug" for="slug" name="slug" :required="true">
            <x-ui.input id="slug" name="slug" :value="old('slug', $paymentMethod->slug ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field name="is_active" :col-span="2">
            <x-ui.checkbox name="is_active" :checked="old('is_active', $paymentMethod->is_active ?? true)">Active</x-ui.checkbox>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
