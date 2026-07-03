<?php

namespace App\Http\Controllers\Marketing;

use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSmsCampaignRequest;
use App\Http\Requests\UpdateSmsCampaignRequest;
use App\Models\SmsCampaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SmsCampaignController extends Controller
{
    use AssignsBranchId;

    public function index(): View
    {
        return view('marketing.sms-campaigns.index', [
            'campaigns' => SmsCampaign::query()->with('creator')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('marketing.sms-campaigns.create');
    }

    public function store(StoreSmsCampaignRequest $request): RedirectResponse
    {
        $campaign = SmsCampaign::create([
            ...$this->withBranchId($request->validated()),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('sms-campaigns.show', $campaign)
            ->with('success', 'SMS campaign created.');
    }

    public function show(SmsCampaign $smsCampaign): View
    {
        return view('marketing.sms-campaigns.show', [
            'campaign' => $smsCampaign->load('creator'),
        ]);
    }

    public function edit(SmsCampaign $smsCampaign): View
    {
        return view('marketing.sms-campaigns.edit', ['campaign' => $smsCampaign]);
    }

    public function update(UpdateSmsCampaignRequest $request, SmsCampaign $smsCampaign): RedirectResponse
    {
        $smsCampaign->update($request->validated());

        return redirect()->route('sms-campaigns.show', $smsCampaign)
            ->with('success', 'SMS campaign updated.');
    }

    public function destroy(SmsCampaign $smsCampaign): RedirectResponse
    {
        $smsCampaign->delete();

        return redirect()->route('sms-campaigns.index')
            ->with('success', 'SMS campaign deleted.');
    }
}
