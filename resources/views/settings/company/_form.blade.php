@php
    $company = $company ?? null;
    $smsNotificationsEnabled = $smsNotificationsEnabled ?? false;
    $commissionsEnabled = $commissionsEnabled ?? false;
    $commissionDefaultRatePercent = $commissionDefaultRatePercent ?? 0;
    $commissionTrigger = $commissionTrigger ?? 'pos_checkout';
    $commissionTriggerOptions = $commissionTriggerOptions ?? [];
    $loyaltyEnabled = $loyaltyEnabled ?? true;
    $loyaltyWashesBeforeFree = $loyaltyWashesBeforeFree ?? 10;
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

<x-ui.form-section title="Commission Settings" description="Supervisors earn commission on each wash. Configure the rate and when it is earned.">
    <div class="asp-form-grid">
        <x-ui.form-field :col-span="2" name="commissions_enabled">
            <div class="asp-checkbox-group">
                <x-ui.checkbox
                    name="commissions_enabled"
                    :checked="old('commissions_enabled', $commissionsEnabled)"
                >
                    Enable supervisor commissions
                </x-ui.checkbox>
            </div>
        </x-ui.form-field>

        <x-ui.form-field label="Default Commission Rate (%)" for="commission_default_rate" name="commission_default_rate">
            <x-ui.input
                id="commission_default_rate"
                name="commission_default_rate"
                type="number"
                step="0.01"
                min="0"
                max="100"
                :value="old('commission_default_rate', $commissionDefaultRatePercent)"
            />
        </x-ui.form-field>

        <x-ui.form-field label="Earn Commission" for="commission_trigger" name="commission_trigger">
            <x-ui.select id="commission_trigger" name="commission_trigger">
                @foreach ($commissionTriggerOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('commission_trigger', $commissionTrigger) === $value)>{{ $label }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>
    </div>
</x-ui.form-section>

<x-ui.form-section title="Loyalty Program" description="Reward repeat customers. After the configured number of paid washes, the next wash is free.">
    <div class="asp-form-grid">
        <x-ui.form-field :col-span="2" name="loyalty_enabled">
            <div class="asp-checkbox-group">
                <x-ui.checkbox
                    name="loyalty_enabled"
                    :checked="old('loyalty_enabled', $loyaltyEnabled)"
                >
                    Enable loyalty program
                </x-ui.checkbox>
            </div>
        </x-ui.form-field>

        <x-ui.form-field
            label="Paid washes before free wash"
            for="loyalty_washes_before_free"
            name="loyalty_washes_before_free"
            hint="Default: 10 paid washes, then the 11th wash is free."
        >
            <x-ui.input
                id="loyalty_washes_before_free"
                name="loyalty_washes_before_free"
                type="number"
                min="1"
                max="100"
                :value="old('loyalty_washes_before_free', $loyaltyWashesBeforeFree)"
            />
        </x-ui.form-field>
    </div>
</x-ui.form-section>
