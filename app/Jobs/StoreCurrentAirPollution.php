<?php

namespace App\Jobs;

use App\Models\AirPollutionHistoricalData;
use App\Services\GiosApi;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;

class StoreCurrentAirPollution implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

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
     * @param  GiosApi  $giosApi
     */
    public function handle(GiosApi $giosApi): void
    {
        $stations = $giosApi->getAllStations();
        if (empty($stations)) {
            return;
        }

        foreach ($stations as $station) {
            $stationId   = data_get($station, 'id');
            $latitude    = (float) data_get($station, 'gegrLat');
            $longitude   = (float) data_get($station, 'gegrLon');
            $stationName = data_get($station, 'stationName');

            $aqIndexData = $giosApi->getAirQualityIndex($stationId);
            $aqIndex     = data_get($aqIndexData, 'stIndexLevel.indexLevelName');

            $sensors      = $giosApi->getStationSensors($stationId) ?: [];
            $measurements = [];
            $pm10 = $pm25 = $no2 = $so2 = $o3 = $co = null;

            foreach ($sensors as $sensor) {
                $paramCode = data_get($sensor, 'param.paramCode');
                $sensorId  = data_get($sensor, 'id');
                $data      = $giosApi->getSensorData($sensorId);

                // Store the full time series in JSON
                $measurements[$paramCode] = $data['values'] ?? [];

                // Parse the latest valid reading
                if (!empty($data['values'])) {
                    $lastValid = $this->getLatestValidValue($data['values']);
                    $value = isset($lastValid['value']) ? (float) $lastValid['value'] : null;

                    switch (strtoupper($paramCode)) {
                        case 'PM10':
                            $pm10 = $value;
                            break;
                        case 'PM2':
                        case 'PM2.5':
                        case 'PM25':
                            $pm25 = $value;
                            break;
                        case 'NO2':
                            $no2 = $value;
                            break;
                        case 'SO2':
                            $so2 = $value;
                            break;
                        case 'O3':
                            $o3 = $value;
                            break;
                        case 'CO':
                            $co = $value;
                            break;
                    }
                }
            }

            // Get forecasts for major pollutants
            $forecasts = [];
            $communeId = data_get($station, 'city.commune.communeId');
            if ($communeId) {
                foreach (['PM10', 'NO2', 'SO2', 'O3'] as $pollutant) {
                    $f = $giosApi->getForecast($pollutant, (string) $communeId);
                    if ($f !== null) {
                        $forecasts[$pollutant] = $f;
                    }
                }
            }

            AirPollutionHistoricalData::create([
                'station_id'        => $$stationId,
                'latitude'          => $latitude,
                'longitude'         => $longitude,
                'station_name'      => $stationName,
                'air_quality_index' => $aqIndex,
                'pm10'              => $pm10,
                'pm25'              => $pm25,
                'no2'               => $no2,
                'so2'               => $so2,
                'o3'                => $o3,
                'co'                => $co,
                'measurements'      => $measurements,
                'forecasts'         => $forecasts,
            ]);
        }
    }

    /**
     * Get the latest valid value from the time series data.
     *
     * @param  array  $values
     * @return array|null
     */
     private function getLatestValidValue(array $values): ?array
     {
         foreach ($values as $value) {
             if (isset($value['value']) && $value['value'] !== null) {
                 return $value;
             }
         }
         return null;
     }
}
