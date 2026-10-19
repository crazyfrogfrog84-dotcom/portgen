<?php

namespace Tests\Feature;

use App\Models\Portfolio;
use Tests\TestCase;

class PortfolioTemplateParityTest extends TestCase
{
    public function test_all_portfolio_templates_share_the_theme_controls_and_section_outline(): void
    {
        $portfolio = Portfolio::factory()->make();
        $templates = ['simple', 'modern', 'creative'];
        $baselineOutline = null;

        foreach ($templates as $template) {
            $html = view('portfolios.templates.'.$template, compact('portfolio'))->render();
            $this->assertStringContainsString('data-template="'.$template.'"', $html);
            $this->assertStringContainsString('data-theme="system"', $html);
            $this->assertSame(3, substr_count($html, 'name="portfolio-theme"'), "{$template} must expose the same three theme options.");
            $this->assertSame(1, preg_match_all('/<h1\b/', $html), "{$template} must render one portfolio name heading.");

            preg_match_all('/<h([1-6])\b[^>]*id="([^"]+)"/', $html, $matches, PREG_SET_ORDER);
            $outline = array_map(fn (array $match) => [$match[1], $match[2]], $matches);
            $baselineOutline ??= $outline;
            $this->assertSame($baselineOutline, $outline, "{$template} changed the shared section heading outline.");
        }
    }
}
