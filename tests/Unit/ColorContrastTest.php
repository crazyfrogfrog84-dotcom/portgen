<?php

namespace Tests\Unit;

use Tests\TestCase;

class ColorContrastTest extends TestCase
{
    public function test_light_and_dark_design_tokens_meet_wcag_contrast_thresholds(): void
    {
        $css = file_get_contents(base_path('resources/css/app.css'));
        $this->assertIsString($css);

        preg_match('/:root\s*\{([^}]*)\}/', $css, $lightMatch);
        preg_match('/@media\s*\(prefers-color-scheme:\s*dark\)[^{]*\{\s*:root\s*\{([^}]*)\}/', $css, $darkMatch);
        $this->assertArrayHasKey(1, $lightMatch, 'Could not read light theme design tokens.');
        $this->assertArrayHasKey(1, $darkMatch, 'Could not read dark theme design tokens.');

        foreach (['light' => $lightMatch[1], 'dark' => $darkMatch[1]] as $mode => $block) {
            preg_match_all('/(--[a-z0-9-]+):\s*(#[0-9a-f]{6})\s*;/i', $block, $matches, PREG_SET_ORDER);
            $tokens = [];
            foreach ($matches as $match) {
                $tokens[$match[1]] = $match[2];
            }

            $pairs = [];
            foreach (['simple', 'modern', 'creative'] as $theme) {
                $pairs[] = ["--{$theme}-foreground", "--{$theme}-background", 4.5];
                $pairs[] = ["--{$theme}-muted", "--{$theme}-background", 4.5];
                $pairs[] = ["--{$theme}-accent", "--{$theme}-background", 4.5];
            }
            foreach ([
                ['--ui-foreground', '--ui-surface', 4.5],
                ['--ui-foreground', '--ui-surface-muted', 4.5],
                ['--ui-muted', '--ui-surface', 4.5],
                ['--ui-control-foreground', '--ui-control-background', 4.5],
                ['--ui-primary-foreground', '--ui-primary-background', 4.5],
                ['--ui-danger', '--ui-danger-background', 4.5],
                ['--ui-success', '--ui-success-background', 4.5],
                ['--focus-ring', '--ui-surface', 3.0],
                ['--ui-border', '--ui-surface', 3.0],
            ] as $pair) {
                $pairs[] = $pair;
            }

            foreach ($pairs as [$foreground, $background, $minimum]) {
                $this->assertArrayHasKey($foreground, $tokens, "Missing {$foreground} in the {$mode} palette.");
                $this->assertArrayHasKey($background, $tokens, "Missing {$background} in the {$mode} palette.");
                $ratio = $this->contrast($tokens[$foreground], $tokens[$background]);
                $this->assertGreaterThanOrEqual(
                    $minimum,
                    $ratio,
                    sprintf('%s against %s is %.2f:1 in %s mode; needs %.1f:1.', $foreground, $background, $ratio, $mode, $minimum)
                );
            }
        }
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
