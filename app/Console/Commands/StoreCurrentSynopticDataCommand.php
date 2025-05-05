<?php

namespace App\Console\Commands;

use App\Jobs\StoreCurrentSynopticData;
use Illuminate\Console\Command;

class StoreCurrentSynopticDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'synoptic:store';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch the StoreCurrentSynopticData job';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        StoreCurrentSynopticData::dispatch();

        $this->info('StoreCurrentSynopticData job has been dispatched.');
    }
}
