@php $supplier = $supplier ?? null; @endphp

<x-ui.form-section title="Supplier Information" description="Contact details and address for this supplier.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Supplier Name" for="name" name="name" :required="true">
            <x-ui.input id="name" name="name" :value="old('name', $supplier->name ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Contact Person" for="contact_person" name="contact_person">
            <x-ui.input id="contact_person" name="contact_person" :value="old('contact_person', $supplier->contact_person ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Phone" for="phone" name="phone">
            <x-ui.input id="phone" name="phone" :value="old('phone', $supplier->phone ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Email" for="email" name="email">
            <x-ui.input id="email" name="email" type="email" :value="old('email', $supplier->email ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Address" for="address" name="address" :col-span="2">
            <x-ui.textarea id="address" name="address" rows="2">{{ old('address', $supplier->address ?? '') }}</x-ui.textarea>
        </x-ui.form-field>

        <x-ui.form-field name="is_active" :col-span="2">
            <x-ui.checkbox name="is_active" :checked="old('is_active', $supplier->is_active ?? true)">Active</x-ui.checkbox>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
