<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Http\Requests\Setup\SetupAdminRequest;
use App\Http\Requests\Setup\SetupBranchRequest;
use App\Http\Requests\Setup\SetupBusinessRequest;
use App\Http\Requests\Setup\SetupPreferencesRequest;
use App\Http\Requests\Setup\SetupTeamRequest;
use App\Services\InstallService;
use App\Services\SetupService;
use App\Support\CommissionSettings;
use Database\Seeders\CoreSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SetupWizardController extends Controller
{
    public const SESSION_KEY = 'setup.wizard';

    public function __construct(
        protected InstallService $installService,
        protected SetupService $setupService,
    ) {}

    public function welcome(): View
    {
        return view('setup.welcome', [
            'requirements' => $this->installService->requirements(),
            'requirementsMet' => $this->installService->requirementsMet(),
        ]);
    }

    public function storeWelcome(Request $request): RedirectResponse
    {
        if (! $this->installService->requirementsMet()) {
            return back()->with('error', 'Please resolve all requirements before continuing.');
        }

        (new CoreSeeder)->run();

        $this->saveWizard(['welcome' => true]);

        return redirect()->route('setup.business');
    }

    public function business(): View|RedirectResponse
    {
        if (! $this->hasStep('welcome')) {
            return redirect()->route('setup.welcome');
        }

        return view('setup.business', [
            'data' => $this->wizard()['business'] ?? [],
        ]);
    }

    public function storeBusiness(SetupBusinessRequest $request): RedirectResponse
    {
        $this->saveWizard(['business' => $request->validated()]);

        return redirect()->route('setup.branch');
    }

    public function branch(): View|RedirectResponse
    {
        if (! $this->hasStep('business')) {
            return redirect()->route('setup.business');
        }

        return view('setup.branch', [
            'data' => $this->wizard()['branch'] ?? [],
        ]);
    }

    public function storeBranch(SetupBranchRequest $request): RedirectResponse
    {
        $this->saveWizard(['branch' => $request->validated()]);

        return redirect()->route('setup.admin');
    }

    public function admin(): View|RedirectResponse
    {
        if (! $this->hasStep('branch')) {
            return redirect()->route('setup.branch');
        }

        return view('setup.admin', [
            'data' => $this->wizard()['admin'] ?? [],
        ]);
    }

    public function storeAdmin(SetupAdminRequest $request): RedirectResponse
    {
        $this->saveWizard(['admin' => $request->validated()]);

        return redirect()->route('setup.team');
    }

    public function team(): View|RedirectResponse
    {
        if (! $this->hasStep('admin')) {
            return redirect()->route('setup.admin');
        }

        return view('setup.team', [
            'data' => $this->wizard()['team'] ?? [],
        ]);
    }

    public function storeTeam(SetupTeamRequest $request): RedirectResponse
    {
        $this->saveWizard(['team' => $request->validated()]);

        return redirect()->route('setup.preferences');
    }

    public function skipTeam(): RedirectResponse
    {
        if (! $this->hasStep('admin')) {
            return redirect()->route('setup.admin');
        }

        $this->saveWizard(['team' => []]);

        return redirect()->route('setup.preferences');
    }

    public function preferences(): View|RedirectResponse
    {
        if (! $this->hasStep('admin')) {
            return redirect()->route('setup.admin');
        }

        return view('setup.preferences', [
            'data' => $this->wizard()['preferences'] ?? [],
            'commissionTriggerOptions' => CommissionSettings::triggerOptions(),
        ]);
    }

    public function storePreferences(SetupPreferencesRequest $request): RedirectResponse
    {
        $this->saveWizard(['preferences' => $request->validated()]);

        return $this->complete();
    }

    public function skipPreferences(): RedirectResponse
    {
        if (! $this->hasStep('admin')) {
            return redirect()->route('setup.admin');
        }

        $this->saveWizard(['preferences' => []]);

        return $this->complete();
    }

    protected function complete(): RedirectResponse
    {
        $wizard = $this->wizard();

        if (! isset($wizard['business'], $wizard['branch'], $wizard['admin'])) {
            return redirect()->route('setup.welcome');
        }

        $this->setupService->install($wizard);

        session()->forget(self::SESSION_KEY);

        return redirect()
            ->route('login')
            ->with('status', 'Setup complete! Sign in with your administrator account to get started.');
    }

    /** @return array<string, mixed> */
    protected function wizard(): array
    {
        return session(self::SESSION_KEY, []);
    }

    /** @param  array<string, mixed>  $data */
    protected function saveWizard(array $data): void
    {
        session([self::SESSION_KEY => array_merge($this->wizard(), $data)]);
    }

    protected function hasStep(string $step): bool
    {
        if ($step === 'welcome') {
            return true;
        }

        $wizard = $this->wizard();

        return match ($step) {
            'business' => isset($wizard['welcome']),
            'branch' => isset($wizard['business']),
            'admin' => isset($wizard['branch']),
            'team', 'preferences' => isset($wizard['admin']),
            default => false,
        };
    }
}
