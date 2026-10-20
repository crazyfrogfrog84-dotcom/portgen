<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AccountAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_and_registration_pages_are_private_and_labelled(): void
    {
        $this->get(route('login'))->assertOk()->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee('for="email"', false)->assertSee('autocomplete="username"', false)
            ->assertSee('autocomplete="current-password"', false)->assertSee('data-password-toggle="password"', false)
            ->assertSee('Remember me on this device')->assertSee('Continue as guest');

        $this->from(route('login'))->followingRedirects()->post(route('login.store'), [
            'email' => 'missing@example.test', 'password' => 'incorrect-password',
        ])
            ->assertSee('aria-live="assertive"', false)->assertSee('The email or password is incorrect.');

        $this->get(route('register'))->assertOk()->assertHeader('Cache-Control', 'no-store, private')
            ->assertSee('for="password_confirmation"', false)->assertSee('autocomplete="new-password"', false)
            ->assertSee('minlength="8"', false)->assertSee('Guests can still create portfolios without registering.')
            ->assertSee('Log in')->assertSee('Sign up');

        $this->get(route('portfolios.create'))->assertSee('Log in')->assertSee('Sign up');
    }

    public function test_registration_validates_password_and_duplicate_email_without_disclosing_account_existence(): void
    {
        $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Casey Example', 'email' => 'casey@example.test', 'password' => 'short', 'password_confirmation' => 'short',
        ])->assertRedirect(route('register'))->assertSessionHasErrors('password')
            ->assertSessionHas('_old_input.name', 'Casey Example')->assertSessionMissing('_old_input.password');

        $this->from(route('register'))->post(route('register.store'), [
            'name' => 'Casey Example', 'email' => 'casey@example.test', 'password' => 'long-enough-password', 'password_confirmation' => 'different-password',
        ])->assertRedirect(route('register'))->assertSessionHasErrors('password');

        $this->post(route('register.store'), [
            'name' => 'Casey Example', 'email' => 'casey@example.test', 'password' => 'long-enough-password', 'password_confirmation' => 'long-enough-password',
        ])->assertRedirect(route('dashboard'))->assertDontSee('long-enough-password');
        $this->assertAuthenticated();
        $this->assertTrue(Hash::check('long-enough-password', User::query()->where('email', 'casey@example.test')->firstOrFail()->password));

        Auth::logout();
        $this->post(route('register.store'), [
            'name' => 'Another Casey', 'email' => 'casey@example.test', 'password' => 'long-enough-password', 'password_confirmation' => 'long-enough-password',
        ])->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_login_uses_one_generic_error_for_unknown_email_and_wrong_password_and_remembers_email(): void
    {
        $user = User::factory()->create(['email' => 'casey@example.test', 'password' => 'correct-password']);

        $wrong = $this->from(route('login'))->post(route('login.store'), [
            'email' => $user->email, 'password' => 'incorrect-password', 'remember' => '1',
        ])->assertRedirect(route('login'))->assertSessionHasErrors(['email' => 'The email or password is incorrect.']);
        $wrong->assertSessionHas('_old_input.email', $user->email)->assertSessionMissing('_old_input.password');

        $unknown = $this->from(route('login'))->post(route('login.store'), [
            'email' => 'unknown@example.test', 'password' => 'incorrect-password',
        ])->assertRedirect(route('login'))->assertSessionHasErrors(['email' => 'The email or password is incorrect.']);
        $this->assertSame(
            $wrong->getSession()->get('errors')->first('email'),
            $unknown->getSession()->get('errors')->first('email')
        );

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'correct-password', 'remember' => '1'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotEmpty($user->fresh()->remember_token);
    }

    public function test_login_locks_after_five_failed_attempts_and_then_recovers(): void
    {
        $user = User::factory()->create(['email' => 'limit@example.test', 'password' => 'correct-password']);
        $key = 'portgen-login:'.hash('sha256', $user->email.'|127.0.0.1');

        for ($attempt = 1; $attempt <= 4; $attempt++) {
            $this->from(route('login'))->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong-password'])->assertRedirect(route('login'));
        }
        $this->from(route('login'))->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertStatus(429)->assertSessionHasErrors('email');
        $this->assertGreaterThan(0, RateLimiter::availableIn($key));

        $this->travel(61)->seconds();
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'correct-password'])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_redirects_only_to_a_safe_same_site_path_and_logout_invalidates_session(): void
    {
        $user = User::factory()->create(['email' => 'casey@example.test']);
        $this->withSession(['url.intended' => 'https://attacker.example/collect'])
            ->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->post(route('logout'))->assertRedirect(route('portfolios.create'))->assertSessionHas('status', 'Logged out.');
        $this->assertGuest();
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }

    public function test_authenticated_creation_assigns_account_and_guest_creation_remains_available(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('portfolios.store'), $this->portfolioData())->assertRedirect();
        $this->assertDatabaseHas('portfolios', ['user_id' => $user->id]);

        Auth::logout();
        $this->post(route('portfolios.store'), $this->portfolioData(['full_name' => 'Guest Portfolio']))->assertRedirect();
        $this->assertDatabaseHas('portfolios', ['full_name' => 'Guest Portfolio', 'user_id' => null]);
    }

    public function test_account_ownership_blocks_other_users_and_guest_signed_links(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $portfolio = Portfolio::factory()->withTemplate('Simple')->create();
        $portfolio->user_id = $owner->id;
        $portfolio->save();
        $oldSignedLink = URL::signedRoute('manage.show', ['portfolio' => $portfolio]);

        $this->actingAs($owner)->get(route('manage.show', ['portfolio' => $portfolio]))->assertOk();
        $this->actingAs($other)->get($oldSignedLink)->assertForbidden();
        Auth::logout();
        $this->get($oldSignedLink)->assertForbidden();
    }

    public function test_signed_guest_portfolio_can_be_claimed_and_its_old_link_stops_working(): void
    {
        $user = User::factory()->create();
        $portfolio = Portfolio::factory()->withTemplate('Simple')->create();
        $oldManageLink = URL::signedRoute('manage.show', ['portfolio' => $portfolio]);

        $this->actingAs($user)->get($oldManageLink)->assertOk()->assertSee('Add this portfolio to your account')
            ->assertSee('this private link will stop granting access')->assertSee('Claim this portfolio');
        $claimLink = URL::signedRoute('manage.claim', ['portfolio' => $portfolio]);
        $this->post($claimLink)->assertRedirect(route('manage.show', ['portfolio' => $portfolio]));
        $this->assertDatabaseHas('portfolios', ['id' => $portfolio->id, 'user_id' => $user->id]);
        Auth::logout();
        $this->get($oldManageLink)->assertForbidden();
        $this->actingAs($user);
        $this->get(route('manage.show', ['portfolio' => $portfolio]))->assertOk();
    }

    public function test_registration_from_the_guest_completion_link_claims_that_portfolio(): void
    {
        $portfolio = Portfolio::factory()->withTemplate('Simple')->create();
        $manageLink = URL::signedRoute('manage.show', ['portfolio' => $portfolio]);

        $this->get(route('register', ['claim_url' => $manageLink]))->assertOk()->assertSee('will be added to this account');
        $this->post(route('register.store'), [
            'name' => 'Portfolio Owner', 'email' => 'owner@example.test', 'password' => 'long-enough-password',
            'password_confirmation' => 'long-enough-password', 'claim_url' => $manageLink,
        ])->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('portfolios', ['id' => $portfolio->id, 'user_id' => User::query()->where('email', 'owner@example.test')->value('id')]);
    }

    public function test_dashboard_lists_only_account_portfolios_and_supports_delete_undo(): void
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $mine = Portfolio::factory()->withTemplate('Modern')->create(['full_name' => 'Mine Portfolio']);
        $mine->user_id = $owner->id;
        $mine->save();
        $theirs = Portfolio::factory()->create(['full_name' => 'Other Person Portfolio']);
        $theirs->user_id = $other->id;
        $theirs->save();

        $this->actingAs($owner)->get(route('dashboard'))->assertOk()->assertSee('Mine Portfolio')
            ->assertSee('Change template')->assertSee(route('portfolio.show', ['portfolio' => $mine]), false)
            ->assertSee(route('manage.edit', ['portfolio' => $mine]), false)
            ->assertSee(route('manage.destroy', ['portfolio' => $mine]), false)
            ->assertSee('Account menu')->assertSee('My portfolios')->assertSee('Log out')
            ->assertDontSee('Other Person Portfolio');
        $deleted = $this->deleteJson(route('manage.destroy', ['portfolio' => $mine]))->assertOk();
        $this->get($deleted->json('deletedUrl'))->assertOk()->assertSee('Undo deletion');
        $this->postJson($deleted->json('undoUrl'))->assertOk()->assertJsonPath('message', 'Deletion undone. Your portfolio is restored.');
        $this->assertDatabaseHas('portfolios', ['id' => $mine->id, 'deleted_at' => null]);
    }

    public function test_dashboard_shows_a_first_portfolio_action_when_empty(): void
    {
        $this->actingAs(User::factory()->create())->get(route('dashboard'))
            ->assertOk()->assertSee('Your portfolio space is ready')
            ->assertSee('Create your first portfolio')
            ->assertSee(route('portfolios.create'), false);
    }

    public function test_account_owner_can_preview_save_and_cancel_a_template_on_the_public_page(): void
    {
        $owner = User::factory()->create();
        $portfolio = Portfolio::factory()->withTemplate('Simple')->create();
        $portfolio->user_id = $owner->id;
        $portfolio->save();
        $publicUrl = route('portfolio.show', ['portfolio' => $portfolio]);

        $this->actingAs($owner)->get(route('portfolio.show', ['portfolio' => $portfolio, 'preview' => 'Creative']))
            ->assertOk()->assertSee('data-template="creative"', false)
            ->assertSee('aria-label="Portfolio owner toolbar"', false)
            ->assertSee('href="'.$publicUrl.'"', false)
            ->assertSee('name="template_choice" value="Creative"', false)
            ->assertSee('>Save</button>', false)->assertSee('>Cancel</a>', false);
        $this->assertSame('Simple', $portfolio->fresh()->template_choice);

        $this->get($publicUrl)->assertOk()->assertSee('data-template="simple"', false);
        $this->post(route('portfolio.save-template', ['portfolio' => $portfolio]), ['template_choice' => 'Creative'])
            ->assertRedirect($publicUrl);
        $this->assertSame('Creative', $portfolio->fresh()->template_choice);
    }

    public function test_visitors_ignore_template_preview_and_never_see_the_owner_toolbar(): void
    {
        $portfolio = Portfolio::factory()->withTemplate('Simple')->create();

        $this->get(route('portfolio.show', ['portfolio' => $portfolio, 'preview' => 'Creative']))
            ->assertOk()->assertSee('data-template="simple"', false)
            ->assertDontSee('data-owner-toolbar', false)
            ->assertDontSee('Portfolio owner toolbar');
    }

    public function test_public_portfolios_have_compact_accessible_theme_controls(): void
    {
        $portfolio = Portfolio::factory()->withTemplate('Simple')->create();

        $this->get(route('portfolio.show', ['portfolio' => $portfolio]))
            ->assertOk()->assertSee('class="pf-theme-toggle"', false)
            ->assertSee('aria-label="Use light theme"', false)
            ->assertSee('aria-label="Use dark theme"', false)
            ->assertSee('aria-label="Use system theme"', false)
            ->assertDontSee('<legend>Theme</legend>', false);
    }

    private function portfolioData(array $overrides = []): array
    {
        return array_merge([
            'full_name' => 'Demo Owner', 'headline' => 'Developer', 'about_me' => 'A short introduction.',
            'skills' => ['PHP'], 'education' => [], 'work_experience' => [], 'projects' => [], 'social_links' => [],
        ], $overrides);
    }
}
