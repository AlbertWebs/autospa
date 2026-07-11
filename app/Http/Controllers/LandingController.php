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

        $phone = $this->sanitizeContactString($company?->phone ?: $branch?->phone);

        return view('landing.index', [
            'company' => $company,
            'companyName' => $this->companyService->displayName(),
            'branch' => $branch,
            'services' => $services,
            'hours' => $hours,
            'dayNames' => $dayNames,
            'locality' => 'Kimana',
            'phone' => $phone !== '' ? $phone : null,
            'whatsappUrl' => $this->whatsappUrlFromPhone($phone),
            'telUrl' => $this->telUrlFromPhone($phone),
            'email' => $this->sanitizeContactString($company?->email ?: $branch?->email) ?: null,
            'address' => $this->sanitizeContactString($company?->address ?: $branch?->address) ?: null,
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

    private function sanitizeContactString(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        return trim(str_replace("\0", '', $value));
    }

    private function whatsappUrlFromPhone(string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?: '';

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '0')) {
            $digits = '254'.substr($digits, 1);
        }

        return 'https://wa.me/'.$digits;
    }

    private function telUrlFromPhone(string $phone): ?string
    {
        if ($phone === '') {
            return null;
        }

        $compact = preg_replace('/\s+/', '', $phone) ?: $phone;

        return 'tel:'.$compact;
    }
}
