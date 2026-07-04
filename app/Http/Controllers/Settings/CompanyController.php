<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\Setting;
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
        ]);
    }

    public function update(UpdateCompanyRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $smsNotificationsEnabled = $request->boolean('sms_notifications_enabled');

        unset($validated['sms_notifications_enabled']);

        Company::query()->firstOrFail()->update($validated);
        Setting::setValue('sms', 'enabled', $smsNotificationsEnabled, null, 'boolean');

        return back()->with('success', 'Company details updated.');
    }
}
