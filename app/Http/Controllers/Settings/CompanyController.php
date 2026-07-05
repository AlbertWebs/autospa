<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\Setting;
use App\Support\CommissionSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function edit(): View
    {
        $this->authorize('viewAny', Setting::class);

        return view('settings.company.edit', [
            'company' => Company::query()->firstOrFail(),
            'smsNotificationsEnabled' => filter_var(
                Setting::getValue('sms', 'enabled', false),
                FILTER_VALIDATE_BOOLEAN
            ),
            'commissionsEnabled' => CommissionSettings::enabled(),
            'commissionDefaultRatePercent' => CommissionSettings::defaultRate() * 100,
            'commissionTrigger' => CommissionSettings::trigger(),
            'commissionTriggerOptions' => CommissionSettings::triggerOptions(),
        ]);
    }

    public function update(UpdateCompanyRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $smsNotificationsEnabled = $request->boolean('sms_notifications_enabled');
        $commissionsEnabled = $request->boolean('commissions_enabled');
        $commissionDefaultRate = $validated['commission_default_rate'] ?? 0;
        $commissionTrigger = $validated['commission_trigger'] ?? CommissionSettings::TRIGGER_POS_CHECKOUT;

        unset(
            $validated['sms_notifications_enabled'],
            $validated['commissions_enabled'],
            $validated['commission_default_rate'],
            $validated['commission_trigger'],
        );

        Company::query()->firstOrFail()->update($validated);
        Setting::setValue('sms', 'enabled', $smsNotificationsEnabled, null, 'boolean');
        Setting::setValue('commission', 'enabled', $commissionsEnabled, null, 'boolean');
        Setting::setValue('commission', 'default_rate', $commissionDefaultRate / 100, null, 'decimal');
        Setting::setValue('commission', 'trigger', $commissionTrigger, null, 'string');

        return back()->with('success', 'Company details updated.');
    }
}
