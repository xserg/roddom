<?php

namespace Tests\Traits;

use Illuminate\Contracts\Console\Kernel;

trait RefreshDatabase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    protected function refreshInMemoryDatabase(): void
    {
        $this->artisan('migrate',
            array_merge(
                $this->migrateUsing(),
                ['--path' => 'database/migrations/tests/2024_01_24_150501_create_mothers_school_table.php'])
        );

        $this->app[Kernel::class]->setArtisan(null);
    }
}
