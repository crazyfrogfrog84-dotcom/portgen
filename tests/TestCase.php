<?php

namespace Tests;

use Dotenv\Dotenv;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $root = dirname(__DIR__);
        $environmentFile = is_file($root.'/.env.testing') ? '.env.testing' : '.env';
        if (is_file($root.'/'.$environmentFile)) {
            Dotenv::createImmutable($root, $environmentFile)->safeLoad();
        }

        $databaseHost = strtolower((string) (getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? '')));
        $databaseUrl = strtolower((string) (getenv('DB_URL') ?: ($_ENV['DB_URL'] ?? $_SERVER['DB_URL'] ?? '')));
        if (str_contains($databaseHost, 'supabase') || str_contains($databaseUrl, 'supabase')) {
            throw new RuntimeException('Tests may not connect to the production Supabase database.');
        }

        parent::setUp();
    }
}
