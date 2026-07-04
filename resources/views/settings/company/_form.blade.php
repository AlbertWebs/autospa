@php
    $company = $company ?? null;
    $smsNotificationsEnabled = $smsNotificationsEnabled ?? false;
@endphp

<x-ui.form-section title="Company Information" description="Legal identity, contact details, and registration information.">
    <div class="asp-form-grid">
        <x-ui.form-field label="Company Name" for="name" name="name" :required="true">
            <x-ui.input id="name" name="name" :value="old('name', $company->name)" required />
        </x-ui.form-field>

        <x-ui.form-field label="Legal Name" for="legal_name" name="legal_name">
            <x-ui.input id="legal_name" name="legal_name" :value="old('legal_name', $company->legal_name)" />
        </x-ui.form-field>

        <x-ui.form-field label="Registration Number" for="registration_number" name="registration_number">
            <x-ui.input id="registration_number" name="registration_number" :value="old('registration_number', $company->registration_number)" />
        </x-ui.form-field>

        <x-ui.form-field label="Tax Number" for="tax_number" name="tax_number">
            <x-ui.input id="tax_number" name="tax_number" :value="old('tax_number', $company->tax_number)" />
        </x-ui.form-field>

        <x-ui.form-field label="Address" for="address" name="address" :col-span="2">
            <x-ui.textarea id="address" name="address" rows="2">{{ old('address', $company->address) }}</x-ui.textarea>
        </x-ui.form-field>

        <x-ui.form-field label="Phone" for="phone" name="phone">
            <x-ui.input id="phone" name="phone" type="tel" :value="old('phone', $company->phone)" />
        </x-ui.form-field>

        <x-ui.form-field label="Email" for="email" name="email">
            <x-ui.input id="email" name="email" type="email" :value="old('email', $company->email)" />
        </x-ui.form-field>

        <x-ui.form-field label="Website" for="website" name="website" :col-span="2">
            <x-ui.input id="website" name="website" :value="old('website', $company->website)" />
        </x-ui.form-field>

        <x-ui.form-field :col-span="2" name="sms_notifications_enabled">
            <div class="asp-checkbox-group">
                <x-ui.checkbox
                    name="sms_notifications_enabled"
                    :checked="old('sms_notifications_enabled', $smsNotificationsEnabled)"
                >
                    Enable SMS notifications for vehicle updates
                </x-ui.checkbox>
            </div>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
