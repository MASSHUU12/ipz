<?php

namespace App\Console\Commands;

use App\Jobs\StoreCurrentAirPollution;
use Illuminate\Console\Command;

class StoreCurrentAirPollutionCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'airpollution:store';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch the StoreCurrentAirPollution job';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        StoreCurrentAirPollution::dispatch();

        $this->info('StoreCurrentAirPollution job has been dispatched.');
    }
}
