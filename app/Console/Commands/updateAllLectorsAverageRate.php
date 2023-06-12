<?php

namespace App\Console\Commands;

use App\Jobs\UpdateAverageLectorRateJob;
use App\Models\Lector;
use Illuminate\Console\Command;

class updateAllLectorsAverageRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-all-lectors-average-rate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $lectors = Lector::query()->with('rates')->get();

        $lectors->each(function (Lector $lector) {
           dispatch_sync(new UpdateAverageLectorRateJob($lector));
        });
    }
}
