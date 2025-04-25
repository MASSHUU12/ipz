<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\DeleteOldAirPollutionData;

class DeleteOldAirPollutionDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'airpollution:delete-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Dispatch the DeleteOldAirPollutionData job';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        DeleteOldAirPollutionData::dispatch();

        $this->info('DeleteOldAirPollutionData job has been dispatched.');
    }
}
