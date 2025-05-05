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
            SynopticHistoricalData::create([
                'station_id' => $item['id_stacji'],
                'station_name' => $item['stacja'],
                'measurement_date' => $item['data_pomiaru'],
                'measurement_hour' => $item['godzina_pomiaru'],
                'temperature' => $item['temperatura'],
                'wind_speed' => $item['predkosc_wiatru'],
                'wind_direction' => $item['kierunek_wiatru'],
                'relative_humidity' => $item['wilgotnosc_wzgledna'],
                'rainfall_total' => $item['suma_opadu'],
                'pressure' => $item['cisnienie']
            ]);
        }
    }
}
