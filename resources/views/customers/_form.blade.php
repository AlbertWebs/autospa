@php $customer = $customer ?? null; @endphp

<x-ui.form-section title="Customer Information" description="Contact details and identification for this customer.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Full Name" for="full_name" name="full_name" :required="true">
            <x-ui.input id="full_name" name="full_name" :value="old('full_name', $customer->full_name ?? '')" required />
        </x-ui.form-field>

        <x-ui.form-field label="Phone" for="phone" name="phone" hint="Optional">
            <x-ui.input id="phone" name="phone" type="tel" :value="old('phone', $customer->phone ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Email" for="email" name="email">
            <x-ui.input id="email" name="email" type="email" :value="old('email', $customer->email ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="ID Number" for="id_number" name="id_number">
            <x-ui.input id="id_number" name="id_number" :value="old('id_number', $customer->id_number ?? '')" />
        </x-ui.form-field>

        <x-ui.form-field label="Address" for="address" name="address" :col-span="2">
            <x-ui.textarea id="address" name="address" rows="2">{{ old('address', $customer->address ?? '') }}</x-ui.textarea>
        </x-ui.form-field>

        <x-ui.form-field label="Notes" for="notes" name="notes" :col-span="2">
            <x-ui.textarea id="notes" name="notes" rows="3">{{ old('notes', $customer->notes ?? '') }}</x-ui.textarea>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
