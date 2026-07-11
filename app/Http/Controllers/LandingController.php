<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicBookingRequest;
use App\Services\CompanyService;
use App\Services\InstallService;
use App\Services\PublicBookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class LandingController extends Controller
{
    public function __construct(
        protected PublicBookingService $publicBookingService,
        protected CompanyService $companyService,
        protected InstallService $installService,
    ) {}

    public function index(): View|RedirectResponse
    {
        if (! $this->installService->isInstalled()) {
            return redirect()->route('setup.welcome');
        }

        $branch = $this->publicBookingService->defaultBranch();
        $company = $this->companyService->company();
        $services = $this->publicBookingService->activeServices($branch?->id);
        $hours = $branch
            ? $this->publicBookingService->businessHours($branch->id)
            : collect();

        $dayNames = [
            0 => 'Sunday',
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
        ];

        return view('landing.index', [
            'company' => $company,
            'companyName' => $this->companyService->displayName(),
            'branch' => $branch,
            'services' => $services,
            'hours' => $hours,
            'dayNames' => $dayNames,
            'locality' => 'Kimana',
            'phone' => $company?->phone ?: $branch?->phone,
            'email' => $company?->email ?: $branch?->email,
            'address' => $company?->address ?: $branch?->address,
        ]);
    }

    public function book(PublicBookingRequest $request): RedirectResponse
    {
        if (! $this->installService->isInstalled()) {
            return redirect()->route('setup.welcome');
        }

        $this->publicBookingService->book($request->validated());

        return redirect()
            ->to(route('landing').'#book')
            ->with('success', 'Request received. We’ll confirm your appointment shortly.');
    }
}
