<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AssignsBranchId;
use App\Http\Requests\StoreEmailCampaignRequest;
use App\Http\Requests\UpdateEmailCampaignRequest;
use App\Models\EmailCampaign;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class EmailCampaignController extends Controller
{
    use AssignsBranchId;

    public function index(): View
    {
        return view('marketing.email-campaigns.index', [
            'campaigns' => EmailCampaign::query()->with('creator')->latest()->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('marketing.email-campaigns.create');
    }

    public function store(StoreEmailCampaignRequest $request): RedirectResponse
    {
        $campaign = EmailCampaign::create([
            ...$this->withBranchId($request->validated()),
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('email-campaigns.show', $campaign)
            ->with('success', 'Email campaign created.');
    }

    public function show(EmailCampaign $emailCampaign): View
    {
        return view('marketing.email-campaigns.show', [
            'campaign' => $emailCampaign->load('creator'),
        ]);
    }

    public function edit(EmailCampaign $emailCampaign): View
    {
        return view('marketing.email-campaigns.edit', ['campaign' => $emailCampaign]);
    }

    public function update(UpdateEmailCampaignRequest $request, EmailCampaign $emailCampaign): RedirectResponse
    {
        $emailCampaign->update($request->validated());

        return redirect()->route('email-campaigns.show', $emailCampaign)
            ->with('success', 'Email campaign updated.');
    }

    public function destroy(EmailCampaign $emailCampaign): RedirectResponse
    {
        $emailCampaign->delete();

        return redirect()->route('email-campaigns.index')
            ->with('success', 'Email campaign deleted.');
    }
}
