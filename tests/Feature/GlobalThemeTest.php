<?php

namespace Tests\Feature;

use Tests\TestCase;

class GlobalThemeTest extends TestCase
{
    public function test_theme_control_is_present_on_application_and_error_pages(): void
    {
        foreach (['/login', '/help', '/portfolios/create', '/missing-theme-page'] as $path) {
            $response = $this->get($path);

            if ($path === '/missing-theme-page') {
                $response->assertNotFound();
            } else {
                $response->assertOk();
            }

            $response->assertSee('data-theme-scope="app"', false)
                ->assertSee('data-app-theme="system"', false)
                ->assertSee('aria-label="Use light theme"', false)
                ->assertSee('aria-label="Use dark theme"', false)
                ->assertSee('aria-label="Use system theme"', false);
        }
    }
}
