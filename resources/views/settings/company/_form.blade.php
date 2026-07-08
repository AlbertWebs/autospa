@php
    $company = $company ?? null;
    $smsNotificationsEnabled = $smsNotificationsEnabled ?? false;
    $commissionsEnabled = $commissionsEnabled ?? false;
    $commissionDefaultRatePercent = $commissionDefaultRatePercent ?? 0;
    $commissionTrigger = $commissionTrigger ?? 'job_completed';
    $commissionTriggerOptions = $commissionTriggerOptions ?? [];
    $commissionPayoutCycle = $commissionPayoutCycle ?? 'daily';
    $commissionPayoutCycleOptions = $commissionPayoutCycleOptions ?? [];
    $loyaltyEnabled = $loyaltyEnabled ?? true;
    $loyaltyWashesBeforeFree = $loyaltyWashesBeforeFree ?? 10;
    $attendanceEnabled = $attendanceEnabled ?? false;
    $posEnabled = $posEnabled ?? true;
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

<x-ui.form-section title="Washer Commission" description="Attendees (wash staff) earn commission on each wash. Supervisors are on fixed salary and are not included here.">
    <div class="asp-form-grid">
        <x-ui.form-field :col-span="2" name="commissions_enabled">
            <div class="asp-checkbox-group">
                <x-ui.checkbox
                    name="commissions_enabled"
                    :checked="old('commissions_enabled', $commissionsEnabled)"
                >
                    Enable attendee commission per wash
                </x-ui.checkbox>
            </div>
        </x-ui.form-field>

        <x-ui.form-field label="Commission Rate (%)" for="commission_default_rate" name="commission_default_rate" hint="Percentage of each wash paid to the assigned attendee.">
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

        <x-ui.form-field label="Earn Commission" for="commission_trigger" name="commission_trigger" hint="When attendee commission is calculated for a completed wash.">
            <x-ui.select id="commission_trigger" name="commission_trigger">
                @foreach ($commissionTriggerOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('commission_trigger', $commissionTrigger) === $value)>{{ $label }}</option>
                @endforeach
            </x-ui.select>
        </x-ui.form-field>

        <x-ui.form-field label="Payout Schedule" for="commission_payout_cycle" name="commission_payout_cycle" hint="Whether washer commissions are settled every day or once per week (Monday–Sunday).">
            <x-ui.select id="commission_payout_cycle" name="commission_payout_cycle">
                @foreach ($commissionPayoutCycleOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('commission_payout_cycle', $commissionPayoutCycle) === $value)>{{ $label }}</option>
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

<x-ui.form-section title="Staff Attendance" description="Track employee clock-in and clock-out when enabled.">
    <div class="asp-form-grid">
        <x-ui.form-field :col-span="2" name="attendance_enabled">
            <div class="asp-checkbox-group">
                <x-ui.checkbox
                    name="attendance_enabled"
                    :checked="old('attendance_enabled', $attendanceEnabled)"
                >
                    Enable staff attendance tracking
                </x-ui.checkbox>
            </div>
        </x-ui.form-field>
    </div>
</x-ui.form-section>

<x-ui.form-section title="Point of Sale" description="Control whether POS appears in navigation and can be accessed by staff.">
    <div class="asp-form-grid">
        <x-ui.form-field :col-span="2" name="pos_enabled">
            <div class="asp-checkbox-group">
                <x-ui.checkbox
                    name="pos_enabled"
                    :checked="old('pos_enabled', $posEnabled)"
                >
                    Enable POS module
                </x-ui.checkbox>
            </div>
        </x-ui.form-field>
    </div>
</x-ui.form-section>
