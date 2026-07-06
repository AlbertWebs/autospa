<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Support\EmailSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailNotificationsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_disables_and_enables_email_notifications(): void
    {
        EmailSettings::enable();

        $this->artisan('notifications:email', ['action' => 'disable'])
            ->assertSuccessful()
            ->expectsOutputToContain('disabled');

        $this->assertFalse(EmailSettings::enabled());

        $this->artisan('notifications:email', ['action' => 'enable'])
            ->assertSuccessful()
            ->expectsOutputToContain('enabled');

        $this->assertTrue(EmailSettings::enabled());
    }

    public function test_command_shows_status(): void
    {
        EmailSettings::disable();

        $this->artisan('notifications:email', ['action' => 'status'])
            ->assertSuccessful()
            ->expectsOutputToContain('disabled');
    }

    protected function setUp(): void
    {
        parent::setUp();

        Setting::setValue('email', 'notifications_enabled', true, null, 'boolean');
    }
}
