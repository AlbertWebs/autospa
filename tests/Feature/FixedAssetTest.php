<?php

namespace Tests\Feature;

use App\Enums\FixedAssetCategory;
use App\Enums\FixedAssetStatus;
use App\Enums\RoleSlug;
use App\Models\Branch;
use App\Models\FixedAsset;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\BranchSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FixedAssetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([RoleSeeder::class, BranchSeeder::class]);
    }

    public function test_manager_can_create_and_view_fixed_asset(): void
    {
        $user = $this->makeManager();
        $branch = Branch::query()->firstOrFail();

        $response = $this->actingAs($user)->post(route('fixed-assets.store'), [
            'name' => 'Pressure Washer',
            'category' => FixedAssetCategory::Equipment->value,
            'status' => FixedAssetStatus::Active->value,
            'location' => 'Bay 2',
            'purchase_cost' => 85000,
            'is_active' => true,
        ]);

        $asset = FixedAsset::query()->firstOrFail();

        $response->assertRedirect(route('fixed-assets.show', $asset));

        $this->assertSame('Pressure Washer', $asset->name);
        $this->assertSame('AST-0001', $asset->asset_tag);
        $this->assertSame($branch->id, $asset->branch_id);

        $this->actingAs($user)
            ->get(route('fixed-assets.index'))
            ->assertOk()
            ->assertSee('Pressure Washer')
            ->assertSee('AST-0001')
            ->assertSee('KES 85,000')
            ->assertSee('Active Asset Value');
    }

    protected function makeManager(): User
    {
        $branch = Branch::query()->firstOrFail();
        $role = Role::query()->where('slug', RoleSlug::Manager->value)->firstOrFail();
        $user = User::factory()->create([
            'branch_id' => $branch->id,
            'email_verified_at' => now(),
        ]);
        $user->roles()->attach($role);
        session(['current_branch_id' => $branch->id]);

        return $user;
    }
}
