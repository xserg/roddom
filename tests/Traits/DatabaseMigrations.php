<?php

namespace Tests\Traits;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabaseState;

trait DatabaseMigrations
{
    use \Illuminate\Foundation\Testing\DatabaseMigrations;

    /**
     * Define hooks to migrate the database before and after each test.
     *
     * @return void
     */
    public function runDatabaseMigrations(): void
    {
        $this->artisan(
            'migrate:fresh',
            array_merge(
                $this->migrateFreshUsing(),
                ['--path' => 'database/migrations/tests/2024_01_24_150501_create_mothers_school_table.php']
            )
        );

        $this->app[Kernel::class]->setArtisan(null);

        $this->beforeApplicationDestroyed(function () {
            $this->artisan('migrate:rollback');

            RefreshDatabaseState::$migrated = false;
        });
    }
}
