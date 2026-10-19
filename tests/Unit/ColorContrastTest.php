<?php

namespace Tests\Unit;

use Tests\TestCase;

class ColorContrastTest extends TestCase
{
    public function test_six_template_palettes_and_neutral_ui_tokens_meet_wcag_contrast_thresholds(): void
    {
        $css = file_get_contents(base_path('resources/css/tokens.css'));
        $this->assertIsString($css);

        preg_match_all(
            '/((?:\s*\[data-template="(?:simple|modern|creative)"\]\[data-theme="(?:light|dark|system)"\]\s*,?)+)\s*\{([^}]*)\}/',
            $css,
            $rules,
            PREG_SET_ORDER
        );

        $palettes = [];
        foreach ($rules as $rule) {
            preg_match_all('/\[data-template="(simple|modern|creative)"\]\[data-theme="(light|dark)"\]/', $rule[1], $selectors, PREG_SET_ORDER);
            if ($selectors === []) {
                continue;
            }

            $tokens = $this->readHexTokens($rule[2]);
            foreach ($selectors as $selector) {
                $palettes[$selector[1].'-'.$selector[2]] = $tokens;
            }
        }

        $this->assertCount(6, $palettes, 'Expected explicit light and dark tokens for all three templates.');
        foreach (['simple', 'modern', 'creative'] as $template) {
            foreach (['light', 'dark'] as $theme) {
                $key = $template.'-'.$theme;
                $this->assertArrayHasKey($key, $palettes, "Missing the {$key} palette.");
                $tokens = $palettes[$key];
                foreach ([
                    ['--text', '--bg', 4.5],
                    ['--muted', '--bg', 4.5],
                    ['--accent', '--bg', 4.5],
                    ['--text', '--surface', 4.5],
                    ['--text', '--surface-2', 4.5],
                    ['--accent-contrast', '--accent', 4.5],
                    ['--danger', '--bg', 4.5],
                    ['--success', '--bg', 4.5],
                    ['--border', '--surface', 3.0],
                    ['--focus', '--bg', 3.0],
                ] as [$foreground, $background, $minimum]) {
                    $this->assertPair($tokens, $foreground, $background, $minimum, $key);
                }
            }
        }

        preg_match('/^:root,\s*:root\[data-app-theme="light"\]\s*\{([^}]*)\}/m', $css, $lightUi);
        preg_match('/^:root\[data-app-theme="dark"\]\s*\{([^}]*)\}/m', $css, $darkUi);
        $this->assertArrayHasKey(1, $lightUi, 'Could not read neutral light UI tokens.');
        $this->assertArrayHasKey(1, $darkUi, 'Could not read neutral dark UI tokens.');

        foreach (['light' => $lightUi[1], 'dark' => $darkUi[1]] as $theme => $block) {
            $tokens = $this->readHexTokens($block);
            foreach ([
                ['--ui-foreground', '--ui-background', 4.5],
                ['--ui-foreground', '--ui-surface', 4.5],
                ['--ui-foreground', '--ui-surface-muted', 4.5],
                ['--ui-muted', '--ui-surface', 4.5],
                ['--ui-control-foreground', '--ui-control-background', 4.5],
                ['--ui-primary-foreground', '--ui-primary-background', 4.5],
                ['--ui-danger', '--ui-danger-background', 4.5],
                ['--ui-success', '--ui-success-background', 4.5],
                ['--ui-focus', '--ui-surface', 3.0],
                ['--ui-border', '--ui-surface', 3.0],
            ] as [$foreground, $background, $minimum]) {
                $this->assertPair($tokens, $foreground, $background, $minimum, 'neutral UI '.$theme);
            }
        }
    }

    /** @return array<string, string> */
    private function readHexTokens(string $block): array
    {
        preg_match_all('/(--[a-z0-9-]+):\s*(#[0-9a-f]{6})\s*;/i', $block, $matches, PREG_SET_ORDER);
        $tokens = [];
        foreach ($matches as $match) {
            $tokens[$match[1]] = $match[2];
        }

        return $tokens;
    }

    /** @param array<string, string> $tokens */
    private function assertPair(array $tokens, string $foreground, string $background, float $minimum, string $palette): void
    {
        $this->assertArrayHasKey($foreground, $tokens, "Missing {$foreground} in {$palette}.");
        $this->assertArrayHasKey($background, $tokens, "Missing {$background} in {$palette}.");
        $ratio = $this->contrast($tokens[$foreground], $tokens[$background]);
        $this->assertGreaterThanOrEqual(
            $minimum,
            $ratio,
            sprintf('%s against %s is %.2f:1 in %s; needs %.1f:1.', $foreground, $background, $ratio, $palette, $minimum)
        );
    }

    private function contrast(string $first, string $second): float
    {
        $luminances = array_map(function (string $hex): float {
            $rgb = array_map(fn (string $channel) => hexdec($channel) / 255, str_split(ltrim($hex, '#'), 2));
            $linear = array_map(fn (float $value) => $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4, $rgb);

            return 0.2126 * $linear[0] + 0.7152 * $linear[1] + 0.0722 * $linear[2];
        }, [$first, $second]);
        rsort($luminances);

        return ($luminances[0] + 0.05) / ($luminances[1] + 0.05);
    }
}
