<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PinUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_pin_can_be_set_from_profile(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/pin', [
                'pin' => '4321',
                'pin_confirmation' => '4321',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('4321', $user->refresh()->pin));
    }

    public function test_existing_pin_must_be_confirmed_before_update(): void
    {
        $user = User::factory()->create([
            'pin' => '1234',
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/pin', [
                'current_pin' => '9999',
                'pin' => '5678',
                'pin_confirmation' => '5678',
            ]);

        $response
            ->assertSessionHasErrorsIn('updatePin', 'current_pin')
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('1234', $user->refresh()->pin));
    }

    public function test_pin_can_be_updated_when_current_pin_is_correct(): void
    {
        $user = User::factory()->create([
            'pin' => '1234',
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->put('/pin', [
                'current_pin' => '1234',
                'pin' => '5678',
                'pin_confirmation' => '5678',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertTrue(Hash::check('5678', $user->refresh()->pin));
    }
}
