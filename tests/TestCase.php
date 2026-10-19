<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $databaseHost = strtolower((string) env('DB_HOST', config('database.connections.pgsql.host', '')));
        if (str_contains($databaseHost, 'supabase')) {
            throw new RuntimeException('Tests may not connect to the production Supabase database.');
        }
    }
}
