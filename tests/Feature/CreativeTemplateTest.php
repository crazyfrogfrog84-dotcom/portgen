<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreativeTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_creative_template_uses_labeled_svg_icons_and_shared_sections(): void
    {
        $portfolio = Portfolio::factory()->create([
            'full_name' => 'Avery Creative',
            'headline' => 'Product designer and developer',
            'about_me' => "I build useful tools.\n\nI enjoy turning ideas into clear interfaces.",
            'location' => 'Taipei',
            'availability' => 'Open to opportunities',
            'education' => [['qualification' => 'BS Computer Science', 'institution' => 'Example University', 'year' => '2025']],
            'interests' => ['Design', 'Music'],
            'skills' => ['HTML5', 'React', 'React'],
            'projects' => [[
                'name' => 'Portfolio project',
                'description' => 'A small project description.',
                'url' => 'https://example.test/project',
                'tags' => ['React'],
            ]],
            'contact_email' => 'avery@example.test',
            'social_links' => [['label' => 'GitHub', 'url' => 'https://example.test/avery']],
            'template_choice' => 'Creative',
        ]);

        $response = $this->get(route('portfolio.show', ['portfolio' => $portfolio]));

        $response->assertOk()
            ->assertSee('aria-label="Creative portfolio template"', false)
            ->assertSee('Quick facts')
            ->assertSee('Portfolio project')
            ->assertSee('View project')
            ->assertSee('Home')
            ->assertSee('About')
            ->assertSee('Projects')
            ->assertSee('Contact')
            ->assertSee('pf-skill-chip__svg', false)
            ->assertSee('class="pf-icon pf-nav__icon"', false)
            ->assertSee('aria-hidden="true" focusable="false"', false)
            ->assertSee('Taipei')
            ->assertSee('BS Computer Science');

        $html = (string) $response->getContent();
        $this->assertSame(1, substr_count($html, 'View project'));
        preg_match('/<ul class="pf-skills-grid[^\"]*pf-skills-grid--creative[^\"]*">(.*?)<\/ul>/s', $html, $skillGrid);
        $this->assertSame(1, substr_count($skillGrid[1] ?? '', 'React</span>'), 'Creative skill chips should remove duplicate entries.');
    }
}
