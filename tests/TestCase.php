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

        $databaseConnection = strtolower((string) (getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? $_SERVER['DB_CONNECTION'] ?? 'sqlite')));
        $databaseHost = strtolower((string) (getenv('DB_HOST') ?: ($_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? '')));
        $databaseName = strtolower((string) (getenv('DB_DATABASE') ?: ($_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? '')));
        $databaseUsername = strtolower((string) (getenv('DB_USERNAME') ?: ($_ENV['DB_USERNAME'] ?? $_SERVER['DB_USERNAME'] ?? '')));
        $databaseUrl = (string) (getenv('DB_URL') ?: ($_ENV['DB_URL'] ?? $_SERVER['DB_URL'] ?? ''));

        if ($databaseUrl !== '') {
            throw new RuntimeException('Tests may not use DB_URL; configure an isolated test database instead.');
        }

        $isLocalPostgresTestDatabase = in_array($databaseHost, ['127.0.0.1', 'localhost'], true)
            && $databaseName === 'portgen_test'
            && $databaseUsername === 'portgen_test';

        if ($databaseConnection === 'pgsql' && ! $isLocalPostgresTestDatabase) {
            throw new RuntimeException('PostgreSQL tests may only connect to the local portgen_test database.');
        }

        if ($databaseConnection === 'sqlite' && $databaseName !== ':memory:') {
            throw new RuntimeException('SQLite tests may only use an in-memory database.');
        }

        if (! in_array($databaseConnection, ['pgsql', 'sqlite'], true)) {
            throw new RuntimeException('Tests may only use the isolated PostgreSQL or in-memory SQLite database.');
        }

        parent::setUp();
    }
}
