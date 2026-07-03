<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateBusinessHourRequest;
use App\Models\BusinessHour;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BusinessHourController extends Controller
{
    public function edit(): View
    {
        $this->authorize('viewAny', Setting::class);

        return view('settings.business-hours.edit', [
            'businessHours' => BusinessHour::query()->orderBy('day_of_week')->get(),
        ]);
    }

    public function update(UpdateBusinessHourRequest $request): RedirectResponse
    {
        $branchId = session('current_branch_id');

        foreach ($request->validated('hours') as $hour) {
            BusinessHour::query()->updateOrCreate(
                ['branch_id' => $branchId, 'day_of_week' => $hour['day_of_week']],
                [...$hour, 'branch_id' => $branchId],
            );
        }

        return back()->with('success', 'Business hours updated.');
    }
}
