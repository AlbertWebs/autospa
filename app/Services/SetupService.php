<?php

namespace App\Services;

use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Support\CommissionSettings;
use App\Support\LoyaltySettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SetupService
{
    public function __construct(
        protected InstallService $installService,
    ) {}

    /** @param  array<string, mixed>  $wizard */
    public function install(array $wizard): void
    {
        DB::transaction(function () use ($wizard) {
            $business = $wizard['business'] ?? [];
            $branchData = $wizard['branch'] ?? [];
            $adminData = $wizard['admin'] ?? [];
            $teamData = $wizard['team'] ?? [];
            $preferences = $wizard['preferences'] ?? [];

            $company = Company::query()->create([
                'name' => $business['name'],
                'legal_name' => $business['legal_name'] ?? null,
                'registration_number' => $business['registration_number'] ?? null,
                'tax_number' => $business['tax_number'] ?? null,
                'address' => $business['address'] ?? null,
                'phone' => $business['phone'] ?? null,
                'email' => $business['email'] ?? null,
                'website' => $business['website'] ?? null,
            ]);

            $branch = Branch::query()->create([
                'name' => $branchData['name'],
                'code' => strtoupper($branchData['code']),
                'address' => $branchData['address'] ?? null,
                'phone' => $branchData['phone'] ?? null,
                'email' => $branchData['email'] ?? null,
                'is_active' => true,
            ]);

            $superAdminRole = Role::query()->where('slug', RoleSlug::SuperAdmin->value)->firstOrFail();

            User::query()->create([
                'name' => $adminData['name'],
                'email' => $adminData['email'],
                'phone' => $adminData['phone'] ?? null,
                'password' => Hash::make($adminData['password']),
                'branch_id' => null,
                'is_active' => true,
                'email_verified_at' => now(),
                'onboarding_completed_at' => null,
            ])->roles()->sync([$superAdminRole->id]);

            $this->createTeamUser($teamData, 'supervisor', RoleSlug::Manager, $branch);

            $this->applyPreferences($preferences);
            $this->applyCompanyDefaults($company->name);

            $this->installService->markInstalled();
        });
    }

    /** @param  array<string, mixed>  $teamData */
    protected function createTeamUser(array $teamData, string $prefix, RoleSlug $roleSlug, Branch $branch): void
    {
        if (! filter_var($teamData['create_supervisor'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $role = Role::query()->where('slug', $roleSlug->value)->first();

        if (! $role) {
            return;
        }

        User::query()->create([
            'name' => $teamData['supervisor_name'],
            'email' => $teamData['supervisor_email'],
            'password' => Hash::make($teamData['supervisor_password']),
            'branch_id' => $branch->id,
            'is_active' => true,
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ])->roles()->sync([$role->id]);
    }

    /** @param  array<string, mixed>  $preferences */
    protected function applyPreferences(array $preferences): void
    {
        Setting::setValue(
            'sms',
            'enabled',
            filter_var($preferences['sms_notifications_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
            null,
            'boolean'
        );

        Setting::setValue(
            'commission',
            'enabled',
            filter_var($preferences['commissions_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN),
            null,
            'boolean'
        );

        Setting::setValue(
            'commission',
            'default_rate',
            ((float) ($preferences['commission_default_rate'] ?? 30)) / 100,
            null,
            'decimal'
        );

        Setting::setValue(
            'commission',
            'trigger',
            $preferences['commission_trigger'] ?? CommissionSettings::TRIGGER_POS_CHECKOUT,
            null,
            'string'
        );

        Setting::setValue(
            'loyalty',
            'enabled',
            filter_var($preferences['loyalty_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            null,
            'boolean'
        );

        Setting::setValue(
            'loyalty',
            'washes_before_free',
            max(1, (int) ($preferences['loyalty_washes_before_free'] ?? LoyaltySettings::DEFAULT_WASHES_BEFORE_FREE)),
            null,
            'integer'
        );
    }

    protected function applyCompanyDefaults(string $companyName): void
    {
        Setting::setValue('receipt', 'footer_text', "Thank you for choosing {$companyName}!", null, 'string');
        Setting::setValue('email', 'from_name', $companyName, null, 'string');
    }
}
