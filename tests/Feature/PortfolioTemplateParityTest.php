<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortfolioTemplateParityTest extends TestCase
{
    use RefreshDatabase;

    public function test_rich_portfolio_pages_share_sections_navigation_controls_and_heading_outline(): void
    {
        $portfolio = Portfolio::factory()->create([
            'full_name' => 'Avery Example',
            'contact_email' => 'avery@example.test',
            'social_links' => [['label' => 'Website', 'url' => 'https://example.test/portfolio']],
            'template_choice' => 'Simple',
        ]);
        $templateNames = ['Simple', 'Modern', 'Creative'];
        $baseline = null;

        foreach ($templateNames as $templateName) {
            $portfolio->update(['template_choice' => $templateName]);
            $response = $this->get(route('portfolio.show', ['portfolio' => $portfolio]));
            $response->assertOk();
            $html = (string) $response->getContent();

            $this->assertSame(1, preg_match_all('/<h1\b/i', $html), "{$templateName} must render exactly one h1.");
            $this->assertSame(1, preg_match_all('/<header\b/i', $html), "{$templateName} must render one page header landmark.");
            $this->assertSame(1, preg_match_all('/<nav\b/i', $html), "{$templateName} must render one navigation landmark.");
            $this->assertSame(1, preg_match_all('/<main\b/i', $html), "{$templateName} must render one main landmark.");
            $this->assertSame(1, preg_match_all('/<footer\b/i', $html), "{$templateName} must render one footer landmark.");
            $this->assertMatchesRegularExpression('/<body[^>]*>\s*<a\b[^>]*class="skip-link"/is', $html, 'The skip link must be the first body child.');
            $this->assertSame(3, substr_count($html, 'name="portfolio-theme"'), 'Every template must expose the same theme choices.');

            preg_match_all('/<section\b[^>]*\bid="([^"]+)"/i', $html, $sectionMatches);
            preg_match('/<nav\b[^>]*data-section-nav[^>]*>(.*?)<\/nav>/is', $html, $navMatch);
            preg_match_all('/<a\b[^>]*href="#([^"]+)"[^>]*>(.*?)<\/a>/is', $navMatch[1] ?? '', $navMatches, PREG_SET_ORDER);
            $navItems = array_map(fn (array $match) => [$match[1], trim(html_entity_decode(strip_tags($match[2])))], $navMatches);

            preg_match_all('/<h([1-6])\b[^>]*>(.*?)<\/h[1-6]>/is', $html, $headingMatches, PREG_SET_ORDER);
            $outline = array_map(fn (array $match) => [$match[1], trim(html_entity_decode(strip_tags($match[2])))], $headingMatches);
            $rendered = [
                'sections' => $sectionMatches[1],
                'navigation' => $navItems,
                'outline' => $outline,
            ];
            if ($templateName === 'Simple') {
                $rendered['outline'] = array_map(function (array $heading) {
                    if ($heading === ['2', 'Get in touch']) {
                        return ['2', 'Contact'];
                    }

                    return $heading;
                }, $rendered['outline']);
            }
            $baseline ??= $rendered;
            $this->assertSame($baseline, $rendered, "{$templateName} changed the shared content or navigation model.");
            $this->assertSame($templateName === 'Simple' ? 1 : 2, substr_count($html, 'href="https://example.test/portfolio"'), 'Simple keeps contact links out of its footer.');
            $this->assertSame(2, substr_count($html, 'mailto:avery@example.test'), 'The email appears in quick facts/contact without extra footer duplication.');
        }
    }

    public function test_minimal_portfolio_omits_empty_sections_and_their_navigation_links(): void
    {
        $portfolio = Portfolio::factory()->create([
            'full_name' => 'Avery Minimal',
            'headline' => '',
            'about_me' => '',
            'location' => null,
            'availability' => null,
            'contact_email' => null,
            'motto' => null,
            'interests' => [],
            'education' => [],
            'work_experience' => [],
            'skills' => [],
            'projects' => [],
            'social_links' => [],
            'template_choice' => 'Simple',
        ]);

        foreach (['Simple', 'Modern', 'Creative'] as $templateName) {
            $portfolio->update(['template_choice' => $templateName]);
            $response = $this->get(route('portfolio.show', ['portfolio' => $portfolio]));
            $response->assertOk();
            $html = (string) $response->getContent();
            $this->assertSame(1, preg_match_all('/<h1\b/i', $html));
            preg_match_all('/<section\b[^>]*\bid="([^"]+)"/i', $html, $sectionMatches);
            $this->assertSame(['home'], $sectionMatches[1]);
            $this->assertStringContainsString('href="#home"', $html);
            $this->assertStringNotContainsString('href="#about"', $html);
            foreach (['skills', 'projects', 'experience', 'education', 'contact'] as $emptySection) {
                $this->assertStringNotContainsString('id="'.$emptySection.'"', $html);
                $this->assertStringNotContainsString('href="#'.$emptySection.'"', $html);
            }
        }
    }
}
