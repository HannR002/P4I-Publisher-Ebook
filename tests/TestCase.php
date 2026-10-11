<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        // Check effective configuration before test traits can migrate or wipe.
        // Cached configuration must never silently redirect tests to live data.
        $connection = $app['config']->get('database.default');
        $database = $app['config']->get('database.connections.'.$connection.'.database');
        $url = $app['config']->get('database.connections.'.$connection.'.url');
        if (! $app->environment('testing') || $url
            || ($connection === 'sqlite' && $database !== ':memory:')
            || ($connection === 'mysql' && ! str_starts_with((string) $database, 'p4i_security_test'))
            || ! in_array($connection, ['sqlite', 'mysql'], true)) {
            throw new \RuntimeException('Tests require testing environment and an isolated database; clear stale configuration caches.');
        }

        return $app;
    }

    protected function setUp(): void
    {
        // Validate the target before Laravel's RefreshDatabase/Migrations traits
        // can run any destructive command.
        $connection = getenv('DB_CONNECTION') ?: 'sqlite';
        if ($connection === 'mysql' && ! str_starts_with(getenv('DB_DATABASE') ?: '', 'p4i_security_test')) {
            throw new \RuntimeException('MySQL tests require an explicitly isolated p4i_security_test database.');
        }
        if (getenv('DB_URL')) {
            throw new \RuntimeException('DB_URL must be empty during isolated test runs.');
        }
        parent::setUp();
    }
}
