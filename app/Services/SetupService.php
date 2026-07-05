<?php

namespace App\Services;

use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\Company;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Support\CommissionSettings;
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
            $this->createTeamUser($teamData, 'cashier', RoleSlug::Cashier, $branch);

            $this->applyPreferences($preferences);
            $this->applyCompanyDefaults($company->name);

            $this->installService->markInstalled();
        });
    }

    /** @param  array<string, mixed>  $teamData */
    protected function createTeamUser(array $teamData, string $prefix, RoleSlug $roleSlug, Branch $branch): void
    {
        $flag = $prefix === 'supervisor' ? 'create_supervisor' : 'create_cashier';

        if (! filter_var($teamData[$flag] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return;
        }

        $role = Role::query()->where('slug', $roleSlug->value)->first();

        if (! $role) {
            return;
        }

        $userData = [
            'name' => $teamData["{$prefix}_name"],
            'email' => $teamData["{$prefix}_email"],
            'password' => Hash::make($teamData["{$prefix}_password"]),
            'branch_id' => $branch->id,
            'is_active' => true,
            'email_verified_at' => now(),
            'onboarding_completed_at' => now(),
        ];

        if ($prefix === 'cashier' && ! empty($teamData['cashier_pin'])) {
            $userData['pin'] = $teamData['cashier_pin'];
        }

        User::query()->create($userData)->roles()->sync([$role->id]);
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
            ((float) ($preferences['commission_default_rate'] ?? 0)) / 100,
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
    }

    protected function applyCompanyDefaults(string $companyName): void
    {
        Setting::setValue('receipt', 'footer_text', "Thank you for choosing {$companyName}!", null, 'string');
        Setting::setValue('email', 'from_name', $companyName, null, 'string');
    }
}
