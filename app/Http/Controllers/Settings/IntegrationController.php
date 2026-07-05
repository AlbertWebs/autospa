<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateIntegrationsRequest;
use App\Models\Integration;
use App\Models\Scopes\BranchScope;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Setting::class);

        return view('settings.integrations.index', [
            'integrations' => Integration::withoutGlobalScope(BranchScope::class)
                ->whereNull('branch_id')
                ->orderBy('provider')
                ->get()
                ->keyBy('provider'),
        ]);
    }

    public function update(UpdateIntegrationsRequest $request): RedirectResponse
    {
        foreach ($request->validated('integrations') as $provider => $data) {
            $integration = Integration::withoutGlobalScope(BranchScope::class)
                ->whereNull('branch_id')
                ->where('provider', $provider)
                ->first();

            if (! $integration) {
                continue;
            }

            $credentials = $integration->credentials ?? [];

            if (! empty($data['access_token'])) {
                $credentials['access_token'] = $data['access_token'];
            }

            $settings = $integration->settings ?? [];

            if (! empty($data['sender_id'])) {
                $settings['sender_id'] = $data['sender_id'];
            }

            $integration->update([
                'is_enabled' => (bool) ($data['enabled'] ?? false),
                'driver' => $data['driver'] ?? $integration->driver,
                'credentials' => $credentials,
                'settings' => $settings,
            ]);

            if ($provider === 'sms' && ! empty($data['sender_id'])) {
                Setting::setValue('sms', 'sender_id', $data['sender_id'], null, 'string');
            }
        }

        return back()->with('success', 'Integrations updated.');
    }
}
