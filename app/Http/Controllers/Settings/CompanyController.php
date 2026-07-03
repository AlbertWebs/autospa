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
        ]);
    }

    public function update(UpdateCompanyRequest $request): RedirectResponse
    {
        Company::query()->firstOrFail()->update($request->validated());

        return back()->with('success', 'Company details updated.');
    }
}
