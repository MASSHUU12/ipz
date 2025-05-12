<?php

namespace App\Jobs;

use App\Models\SynopticHistoricalData;
use App\Services\ImgwApiClient;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class StoreCurrentSynopticData implements ShouldQueue, ShouldBeUnique
{
    use Queueable, Dispatchable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @param  ImgwApiClient  $api
     */
    public function handle(ImgwApiClient $api): void
    {
        $response = $api->getSynopData();

        if ($response === null) {
            Log::info('[StoreCurrentSynopticData] There is no synoptic data to add.');
            return;
        }

        foreach ($response as $item) {
            SynopticHistoricalData::create(ImgwApiClient::synopFromRaw($item));
        }
    }
}
