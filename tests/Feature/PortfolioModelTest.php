<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use Database\Seeders\PortfolioSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class PortfolioModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_model_uses_a_uuid_primary_key_and_slug_route_key(): void
    {
        $portfolio = Portfolio::factory()->create();

        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/i', $portfolio->id);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{10}$/', $portfolio->slug);
        $this->assertSame('slug', $portfolio->getRouteKeyName());
    }

    public function test_mass_assignment_ignores_id_slug_and_timestamps(): void
    {
        $portfolio = Portfolio::create([
            'id' => '11111111-1111-4111-8111-111111111111',
            'slug' => 'attacker001',
            'created_at' => '2000-01-01 00:00:00',
            'updated_at' => '2000-01-01 00:00:00',
            'full_name' => 'Avery Example',
            'headline' => 'Designer',
            'about_me' => 'A short introduction.',
            'education' => [],
            'work_experience' => [],
            'skills' => ['Design'],
            'projects' => [],
            'social_links' => [],
        ]);

        $this->assertNotSame('11111111-1111-4111-8111-111111111111', $portfolio->id);
        $this->assertNotSame('attacker001', $portfolio->slug);
        $this->assertNotSame('2000-01-01 00:00:00', $portfolio->created_at->format('Y-m-d H:i:s'));
    }

    public function test_slug_is_unique(): void
    {
        $first = Portfolio::factory()->create();
        $second = Portfolio::factory()->make();
        $second->slug = $first->slug;

        $this->expectException(QueryException::class);
        $second->save();
    }

    public function test_json_columns_round_trip_as_structured_arrays(): void
    {
        $payload = [
            'education' => [['institution' => 'Northstar University', 'qualification' => 'B.A.', 'year' => '2025']],
            'work_experience' => [['company' => 'Studio North', 'role' => 'Designer', 'description' => 'Built a design system.', 'start_date' => '2024-01', 'end_date' => null]],
            'skills' => ['Research', 'Design'],
            'projects' => [['name' => 'Transit Map', 'description' => 'A public transit guide.', 'url' => 'https://example.test/project']],
            'social_links' => [['label' => 'LinkedIn', 'url' => 'https://linkedin.com/in/avery']],
        ];

        $portfolio = Portfolio::factory()->create($payload);
        $loaded = Portfolio::query()->findOrFail($portfolio->id);

        foreach ($payload as $column => $value) {
            $this->assertSame($value, $loaded->{$column});
        }
    }

    public function test_database_rejects_template_choices_outside_the_supported_set(): void
    {
        $portfolio = Portfolio::factory()->make();
        $values = $portfolio->getAttributes();
        $values['id'] = (string) Str::uuid();
        $values['slug'] = 'badchoice1';
        $values['template_choice'] = 'Unlisted';
        $values['created_at'] = now();
        $values['updated_at'] = now();

        try {
            DB::table('portfolios')->insert($values);
            $this->fail('The database accepted an unsupported template choice.');
        } catch (QueryException $exception) {
            $message = $exception->getMessage();
            $this->assertTrue(
                str_contains($message, 'Invalid template_choice')
                    || str_contains($message, 'portfolios_template_choice_check'),
                'The database rejected the row for a reason other than the template-choice constraint.'
            );
        }
    }

    public function test_portfolio_seeder_creates_sample_portfolios(): void
    {
        $this->seed(PortfolioSeeder::class);

        $this->assertDatabaseCount('portfolios', 3);
    }
}
