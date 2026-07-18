<?php

namespace Tests\Feature;

use Tests\TestCase;

class PWATest extends TestCase
{
    /** @test */
    public function manifest_json_is_accessible(): void
    {
        $response = $this->get('/manifest.json');
        $response->assertStatus(200);
    }

    /** @test */
    public function manifest_contains_required_fields(): void
    {
        $response = $this->get('/manifest.json');
        $manifest = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('name', $manifest);
        $this->assertArrayHasKey('short_name', $manifest);
        $this->assertArrayHasKey('start_url', $manifest);
        $this->assertArrayHasKey('display', $manifest);
        $this->assertArrayHasKey('icons', $manifest);
        $this->assertEquals('standalone', $manifest['display']);
    }

    /** @test */
    public function service_worker_is_accessible(): void
    {
        $response = $this->get('/sw.js');
        $response->assertStatus(200);
    }

    /** @test */
    public function pwa_icons_are_accessible(): void
    {
        $response = $this->get('/icons/icon-192x192.png');
        $response->assertStatus(200);
    }

    /** @test */
    public function login_page_contains_pwa_meta_tags(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('manifest.json');
        $response->assertSee('theme-color');
    }
}
