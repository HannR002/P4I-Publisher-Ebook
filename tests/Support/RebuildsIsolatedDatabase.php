<?php

namespace Tests\Support;

use Illuminate\Foundation\Testing\RefreshDatabaseState;

/** DDL and multi-process tests cannot use an outer transaction. */
trait RebuildsIsolatedDatabase
{
    protected function rebuildIsolatedDatabase(): void
    {
        $this->artisan('migrate:fresh', ['--force' => true])->assertSuccessful();
    }

    protected function tearDown(): void
    {
        if (isset($this->app)) {
            $this->artisan('db:wipe', ['--force' => true])->assertSuccessful();
            RefreshDatabaseState::$migrated = false;
            RefreshDatabaseState::$inMemoryConnections = [];
        }
        parent::tearDown();
    }
}
