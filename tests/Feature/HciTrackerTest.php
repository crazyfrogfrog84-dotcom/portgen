<?php

namespace Tests\Feature;

use Tests\TestCase;

class HciTrackerTest extends TestCase
{
    public function test_tracker_contains_every_required_principle_and_only_existing_paths(): void
    {
        $tracker = file_get_contents(base_path('docs/HCI_TRACKER.md'));
        $this->assertIsString($tracker);

        $expected = [];
        foreach (['S' => 8, 'N' => 7, 'H' => 10, 'U' => 7, 'C' => 5, 'V' => 8, 'R' => 3] as $prefix => $count) {
            for ($number = 1; $number <= $count; $number++) {
                $expected[] = $prefix.$number;
            }
        }

        preg_match_all('/^\|\s*([SNHUCVR]\d+)\s*\|([^\n]*)$/m', $tracker, $matches, PREG_SET_ORDER);
        $rows = [];
        foreach ($matches as $match) {
            $cells = array_map('trim', explode('|', $match[0]));
            $cells = array_slice($cells, 1, -1);
            if (count($cells) === 6) {
                $rows[$cells[0]] = $cells;
            }
        }

        $this->assertSame([], array_values(array_diff($expected, array_keys($rows))), 'The tracker is missing required IDs.');
        $this->assertSame([], array_values(array_diff(array_keys($rows), $expected)), 'The tracker contains unexpected IDs.');

        foreach ($rows as $id => $cells) {
            $paths = preg_split('/\s*(?:<br\s*\/?>|;)\s*/i', str_replace('`', '', $cells[2])) ?: [];
            $this->assertNotSame([], $paths, "{$id} must name an application or documentation path.");
            foreach ($paths as $path) {
                $path = trim($path);
                $this->assertNotSame('', $path, "{$id} contains an empty path.");
                $this->assertFileExists(base_path($path), "{$id} references missing path {$path}.");
            }

            if (trim($cells[5]) === 'Verified') {
                $this->assertNotSame('—', trim($cells[4]), "{$id} is Verified without evidence.");
            }

            if (env('HCI_RELEASE') === '1') {
                $this->assertSame('Verified', trim($cells[5]), "{$id} is not Verified for an HCI release.");
            }
        }
    }
}
