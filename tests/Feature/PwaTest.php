<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaTest extends TestCase
{
    use RefreshDatabase;

    public function test_manifest_is_available_with_required_install_fields(): void
    {
        $response = $this->get(route('manifest'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/manifest+json');

        $response->assertJsonStructure([
            'name',
            'short_name',
            'description',
            'start_url',
            'scope',
            'display',
            'theme_color',
            'background_color',
            'icons',
        ]);

        $response->assertJsonPath('display', 'standalone');
        $response->assertJsonCount(3, 'icons');
    }

    public function test_service_worker_route_is_publicly_accessible(): void
    {
        $response = $this->get(route('service-worker'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/javascript; charset=utf-8');
        $response->assertSee('autospa-pages-v4', false);
    }

    public function test_service_worker_file_is_publicly_accessible(): void
    {
        $this->assertFileExists(public_path('sw.js'));

        $contents = file_get_contents(public_path('sw.js'));

        $this->assertStringContainsString('autospa-pages-v4', $contents);
        $this->assertStringContainsString('serveHtml', $contents);
        $this->assertStringContainsString('matchCachedPage', $contents);
    }

    public function test_pwa_icons_are_publicly_accessible(): void
    {
        $this->assertFileExists(public_path('logo.png'));
    }
}
