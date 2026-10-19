<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PortfolioRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_redirects_to_the_portfolio_form(): void
    {
        $this->get('/')->assertRedirect(route('portfolios.create'));
    }

    public function test_create_form_loads_and_has_security_headers(): void
    {
        $response = $this->get(route('portfolios.create'));

        $response->assertOk()
            ->assertSee('Create your portfolio');
        $this->assertNotEmpty($response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("frame-ancestors 'none'", $response->headers->get('Content-Security-Policy'));
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    public function test_health_endpoint_is_available(): void
    {
        $this->get('/up')->assertOk()->assertSeeText('OK');
    }

    public function test_store_creates_a_portfolio_and_redirects_to_template_selection(): void
    {
        $response = $this->post(route('portfolios.store'), $this->validPortfolioData());
        $portfolio = Portfolio::query()->firstOrFail();

        $response->assertRedirect(route('portfolio.select-template', ['portfolio' => $portfolio]));
        $this->assertSame(1, Portfolio::count());
        $this->assertNotEmpty(session('ownerManageUrl'));
        $this->assertTrue(URL::hasValidSignature(request()->create(session('ownerManageUrl'))));
    }

    public function test_store_requires_a_skill_and_rejects_unsafe_urls(): void
    {
        $this->postJson(route('portfolios.store'), $this->validPortfolioData(['skills' => []]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['skills'])
            ->assertJsonPath('errors.skills.0', 'Add at least one skill.');

        $this->postJson(route('portfolios.store'), $this->validPortfolioData([
            'projects' => [['name' => 'Portfolio', 'description' => 'Example project', 'url' => 'javascript:alert(1)']],
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['projects.0.url']);

        $this->assertDatabaseCount('portfolios', 0);
    }

    public function test_template_selection_uses_and_preserves_the_private_link_for_the_creator(): void
    {
        $storeResponse = $this->post(route('portfolios.store'), $this->validPortfolioData());
        $selectUrl = $storeResponse->headers->get('Location');
        $selection = $this->get($selectUrl);

        $selection->assertOk()
            ->assertSee('Back to your details')
            ->assertDontSee('Save your private manage link');

        $portfolio = Portfolio::query()->firstOrFail();
        $saveResponse = $this->post(route('portfolio.save-template', ['portfolio' => $portfolio]), ['template_choice' => 'Modern']);

        $saveResponse->assertRedirect(route('portfolio.show', ['portfolio' => $portfolio]));
        $this->get($saveResponse->headers->get('Location'))
            ->assertOk()
            ->assertSee('Step 3 of 3')
            ->assertSee('Your portfolio is ready')
            ->assertSee('Copy private link');
        $this->get($saveResponse->headers->get('Location'))->assertDontSee('Copy private link');
        $this->assertSame('Modern', $portfolio->fresh()->template_choice);
    }

    public function test_template_choice_is_limited_to_the_three_supported_values(): void
    {
        $portfolio = Portfolio::factory()->create();

        $this->postJson(route('portfolio.save-template', ['portfolio' => $portfolio]), ['template_choice' => 'Custom'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['template_choice']);

        $this->assertNull($portfolio->fresh()->template_choice);
    }

    public function test_public_portfolio_renders_the_chosen_template(): void
    {
        $portfolio = Portfolio::factory()->withTemplate('Creative')->create();

        $this->get(route('portfolio.show', ['portfolio' => $portfolio]))
            ->assertOk()
            ->assertSee('Creative portfolio template')
            ->assertSee($portfolio->full_name)
            ->assertSee($portfolio->projects[0]['name'])
            ->assertDontSee('Copy private link');
    }

    public function test_a_portfolio_without_a_template_returns_to_template_selection(): void
    {
        $portfolio = Portfolio::factory()->create();

        $this->get(route('portfolio.show', ['portfolio' => $portfolio]))
            ->assertRedirect(route('portfolio.select-template', ['portfolio' => $portfolio]));
    }

    public function test_signed_manage_link_opens_and_unsigned_link_is_forbidden(): void
    {
        $portfolio = Portfolio::factory()->withTemplate('Simple')->create();

        $this->get(URL::signedRoute('manage.show', ['portfolio' => $portfolio]))
            ->assertOk()
            ->assertSee('This private link lets you edit or delete this portfolio');
        $query = parse_url(URL::signedRoute('manage.show', ['portfolio' => $portfolio]), PHP_URL_QUERY);
        $this->assertStringNotContainsString('expires=', (string) $query);

        $this->get(route('manage.show', ['portfolio' => $portfolio]))
            ->assertForbidden()
            ->assertSee('This private link is not valid');
    }

    public function test_invalid_signature_cannot_edit_or_delete_a_portfolio(): void
    {
        $portfolio = Portfolio::factory()->create();
        $editUrl = URL::signedRoute('manage.edit', ['portfolio' => $portfolio]);
        $tamperedUrl = str_replace('signature=', 'signature=invalid', $editUrl);

        $this->get($tamperedUrl)->assertForbidden();
        $this->delete(route('manage.destroy', ['portfolio' => $portfolio]))->assertForbidden();
        $this->assertDatabaseHas('portfolios', ['id' => $portfolio->id, 'deleted_at' => null]);
    }

    public function test_signed_edit_and_update_work(): void
    {
        $portfolio = Portfolio::factory()->withTemplate('Simple')->create();
        $this->get(URL::signedRoute('manage.edit', ['portfolio' => $portfolio]))
            ->assertOk()
            ->assertSee('Edit your portfolio');

        $response = $this->put(
            URL::signedRoute('manage.update', ['portfolio' => $portfolio]),
            $this->validPortfolioData(['full_name' => 'Avery Updated'])
        );

        $response->assertRedirect();
        $this->assertSame('Avery Updated', $portfolio->fresh()->full_name);
    }

    public function test_missing_portfolios_render_the_friendly_not_found_page(): void
    {
        $this->get('/p/abcdefghij')
            ->assertNotFound()
            ->assertSee('We could not find that page');
    }

    public function test_expired_form_and_server_error_have_friendly_pages(): void
    {
        config(['app.debug' => false]);
        Route::get('/_test/errors/419', fn () => abort(419));
        Route::get('/_test/errors/500', fn () => abort(500));

        $this->get('/_test/errors/419')
            ->assertStatus(419)
            ->assertSee('This form has expired');
        $this->get('/_test/errors/500')
            ->assertServerError()
            ->assertSee('Something went wrong');
    }

    public function test_all_write_routes_are_rate_limited(): void
    {
        $writeRouteNames = [
            'portfolios.store',
            'portfolio.save-template',
            'manage.update',
            'manage.destroy',
            'manage.undo',
            'manage.purge',
        ];

        foreach ($writeRouteNames as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route);
            $this->assertTrue(
                collect($route->gatherMiddleware())->contains(fn ($middleware) => str_starts_with($middleware, 'throttle:20,1')),
                "{$name} must have the write throttle middleware."
            );
        }
    }

    public function test_store_is_throttled_with_a_friendly_429_response(): void
    {
        Cache::flush();
        for ($request = 0; $request < 20; $request++) {
            $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.89'])
                ->post(route('portfolios.store'), [])
                ->assertRedirect();
        }

        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.89'])
            ->post(route('portfolios.store'), [])
            ->assertTooManyRequests()
            ->assertSee('Too many requests');
    }

    public function test_delete_can_be_undone_during_the_ten_second_window(): void
    {
        $portfolio = Portfolio::factory()->withTemplate('Simple')->create();
        $deleteUrl = URL::signedRoute('manage.destroy', ['portfolio' => $portfolio]);

        $deleted = $this->deleteJson($deleteUrl)->assertOk()
            ->assertJsonPath('message', 'Portfolio deleted. You have 10 seconds to undo.');
        $this->assertSoftDeleted('portfolios', ['id' => $portfolio->id]);
        $this->get(URL::signedRoute('manage.deleted', ['portfolio' => $portfolio]))
            ->assertOk()
            ->assertSee('Undo deletion');

        $this->postJson($deleted->json('undoUrl'))
            ->assertOk()
            ->assertJsonPath('message', 'Deletion undone. Your portfolio is restored.');
        $this->assertDatabaseHas('portfolios', ['id' => $portfolio->id, 'deleted_at' => null, 'undo_until' => null]);
    }

    public function test_delete_is_permanent_after_the_undo_window(): void
    {
        $portfolio = Portfolio::factory()->withTemplate('Simple')->create();
        $deleteUrl = URL::signedRoute('manage.destroy', ['portfolio' => $portfolio]);
        $deleted = $this->deleteJson($deleteUrl)->assertOk();

        $this->travel(11)->seconds();
        $this->deleteJson($deleted->json('purgeUrl'))
            ->assertOk()
            ->assertJsonPath('message', 'Portfolio permanently deleted.');
        $this->assertDatabaseMissing('portfolios', ['id' => $portfolio->id]);
    }

    private function validPortfolioData(array $overrides = []): array
    {
        return array_replace([
            'full_name' => 'Avery Example',
            'headline' => 'Designer building useful tools',
            'about_me' => 'I make thoughtful digital products.',
            'education' => [['institution' => 'Northstar University', 'qualification' => 'B.A. Design', 'year' => '2025']],
            'work_experience' => [],
            'skills' => ['Research', 'Design'],
            'projects' => [['name' => 'Transit Map', 'description' => 'A clear city transit guide.', 'url' => 'https://example.test/transit']],
            'social_links' => [['label' => 'LinkedIn', 'url' => 'https://www.linkedin.com/in/avery-example']],
        ], $overrides);
    }
}
