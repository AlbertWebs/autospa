<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateIntegrationRequest;
use App\Models\Integration;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class IntegrationController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Setting::class);

        return view('settings.integrations.index', [
            'integrations' => Integration::query()->orderBy('provider')->get(),
        ]);
    }

    public function update(UpdateIntegrationRequest $request, Integration $integration): RedirectResponse
    {
        $integration->update($request->validated());

        return back()->with('success', 'Integration updated.');
    }
}
