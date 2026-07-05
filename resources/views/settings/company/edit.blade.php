<x-ui.form-page
    eyebrow="Settings"
    title="Company Settings"
    subtitle="Manage your company profile and legal information."
    panel-title="Company Details"
    panel-icon="business"
    :action="route('settings.company.update')"
    method="PUT"
    submit-label="Save Company Details"
>
    @include('settings.company._form', [
        'company' => $company,
        'smsNotificationsEnabled' => $smsNotificationsEnabled,
        'commissionsEnabled' => $commissionsEnabled,
        'commissionDefaultRatePercent' => $commissionDefaultRatePercent,
        'commissionTrigger' => $commissionTrigger,
        'commissionTriggerOptions' => $commissionTriggerOptions,
    ])
</x-ui.form-page>
