<?php

namespace Tests\Feature;

use Tests\TestCase;

class DesktopRuntimeTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('APP_RUNTIME');
        putenv('DESKTOP_REMOTE_URL');
        unset($_ENV['APP_RUNTIME'], $_SERVER['APP_RUNTIME']);
        unset($_ENV['DESKTOP_REMOTE_URL'], $_SERVER['DESKTOP_REMOTE_URL']);

        parent::tearDown();
    }

    public function test_desktop_config_is_disabled_by_default(): void
    {
        $this->assertFalse(config('desktop.enabled'));
    }

    public function test_desktop_config_is_enabled_when_app_runtime_is_electron(): void
    {
        putenv('APP_RUNTIME=electron');
        $_ENV['APP_RUNTIME'] = 'electron';
        $_SERVER['APP_RUNTIME'] = 'electron';

        $this->refreshApplication();

        $this->assertTrue(config('desktop.enabled'));
        $this->assertTrue(config('desktop.auto_sync'));
    }

    public function test_desktop_remote_sync_url_reads_from_env(): void
    {
        putenv('DESKTOP_REMOTE_URL=https://autospa.example.com');
        $_ENV['DESKTOP_REMOTE_URL'] = 'https://autospa.example.com';
        $_SERVER['DESKTOP_REMOTE_URL'] = 'https://autospa.example.com';

        $this->refreshApplication();

        $this->assertSame('https://autospa.example.com', config('desktop.remote_sync_url'));
    }
}
