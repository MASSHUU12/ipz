<?php

namespace App\Services;

use App\Data\StationData;
use App\Models\AirPollutionHistoricalData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AirQualityService
{
    protected const STATION_CACHE_TTL     = 1440; // minutes
    protected const AIR_QUALITY_CACHE_TTL =   30; // minutes
    protected const SENSOR_CACHE_TTL      =   60; // minutes
    protected const FORECAST_CACHE_TTL    =   60; // minutes

    public function __construct(protected GiosApi $giosApi) {}

    public function getForCoordinates(float $lat, float $lon): array
    {
        [$cLat, $cLon] = [round($lat, 3), round($lon, 3)];

        $station = $this->getNearestStation($lat, $lon, $cLat, $cLon);
        $historical = $this->getRecentHistorical($station->id);

        if ($historical) {
            return $this->formatFromHistorical($historical, $station, $lat, $lon);
        }

        $fresh   = $this->fetchFreshData($station);
        $payload = $this->mergeAndPersist($fresh, $station, $cLat, $cLon);

        return $this->formatFresh($payload, $station, $lat, $lon);
    }

    protected function getNearestStation(
        float $lat,
        float $lon,
        float $cLat,
        float $cLon
    ): StationData {
        $raw = Cache::remember(
            "station_{$cLat}_{$cLon}",
            self::STATION_CACHE_TTL * 60,
            fn() => $this->giosApi->findNearestStation($lat, $lon)
        );

        if (!is_array($raw) || empty($raw)) {
            throw new \RuntimeException('No measuring stations found');
        }

        return StationData::fromApi($raw);
    }

    protected function getRecentHistorical(int $stationId): ?AirPollutionHistoricalData
    {
        $historical = AirPollutionHistoricalData::where('station_id', $stationId)
            ->latest('created_at')
            ->first();

        if ($historical && $historical->created_at->gt(
                now()->subMinutes(self::AIR_QUALITY_CACHE_TTL)
            )
        ) {
            return $historical;
        }

        return null;
    }

    protected function fetchFreshData(StationData $st): array
    {
        $stationId = $st->id;

        $aqCacheKey = "air_quality_index_{$stationId}";
        $aqi = Cache::remember(
            $aqCacheKey,
            self::AIR_QUALITY_CACHE_TTL * 60,
            fn() => $this->giosApi->getAirQualityIndex($stationId)
        );

        $sensorsCacheKey = "station_sensors_{$stationId}";
        $sensors = Cache::remember(
            $sensorsCacheKey,
            self::STATION_CACHE_TTL * 60,
            fn() => $this->giosApi->getStationSensors($stationId)
        );

        $measurements = [];
        if (!empty($sensors)) {
            foreach ($sensors as $sensor) {
                $sensorId = $sensor['id'];
                $dataKey  = "sensor_data_{$sensorId}";

                $sensorData = Cache::remember(
                    $dataKey,
                    self::SENSOR_CACHE_TTL * 60,
                    fn() => $this->giosApi->getSensorData($sensorId)
                );

                if (! empty($sensorData['values'])) {
                    foreach ($sensorData['values'] as $v) {
                        if ($v['value'] !== null) {
                            $measurements[] = [
                                'parameter'       => $sensor['param']['paramName'],
                                'code'            => $sensor['param']['paramCode'],
                                'value'           => $v['value'],
                                'unit'            => 'µg/m³',
                                'measurementTime' => $v['date'],
                            ];
                            break;
                        }
                    }
                }
            }
        }

        // Forecasts (try station’s city‐ID, then fallback 1465)
        $forecasts = [];
        $terytCodes = array_filter([
            $st->communeId, // the city/commune TERYT code
            '1465',         // fallback to Warsaw
        ]);

        foreach (['PM10', 'NO2', 'SO2', 'O3'] as $pollutant) {
            foreach ($terytCodes as $code) {
                $fKey = "forecast_{$pollutant}_{$code}";
                $fData = Cache::remember(
                    $fKey,
                    self::FORECAST_CACHE_TTL * 60,
                    fn() => $this->giosApi->getForecast($pollutant, $code)
                );
                if ($fData) {
                    $forecasts[$pollutant] = $fData;
                    break 2;
                }
            }
        }

        // Shape the index info
        $indexInfo = [
            'index'           => $aqi['stIndexLevel']['indexLevelName'] ?? 'No data',
            'calculationTime' => $aqi['stCalcDate']       ?? null,
            'sourceDataTime'  => $aqi['stSourceDataDate'] ?? null,
        ];
        foreach (['pm10','pm25','o3','no2','so2','co'] as $p) {
            $idxKey = "{$p}IndexLevel";
            $dtKey  = "{$p}CalcDate";
            if (isset($aqi[$idxKey])) {
                $indexInfo['pollutants'][$p] = [
                    'index'           => $aqi[$idxKey]['indexLevelName'] ?? 'No data',
                    'calculationTime' => $aqi[$dtKey]                    ?? null,
                ];
            }
        }

        return [
            'indexInfo'   => $indexInfo,
            'measurements'=> $measurements,
            'forecasts'   => $forecasts,
        ];
    }

    protected function mergeAndPersist(
        array $fresh,
        StationData $st,
        float $cLat,
        float $cLon
    ): array
    {
        try {
            $getVal = fn(string $code) => optional(
                collect($fresh['measurements'])
                    ->firstWhere('code', $code)
            )['value'] ?? null;

            AirPollutionHistoricalData::updateOrCreate(
                [
                    'station_id'        => $st->id,
                    'latitude'          => $cLat,
                    'longitude'         => $cLon,
                    'station_name'      => $st->name,
                    'air_quality_index' => $fresh['indexInfo']['index'] ?? null,
                    'pm10'              => $getVal('PM10'),
                    'pm25'              => $getVal('PM2.5'),
                    'no2'               => $getVal('NO2'),
                    'so2'               => $getVal('SO2'),
                    'o3'                => $getVal('O3'),
                    'co'                => $getVal('CO'),
                    'measurements'      => $fresh['measurements'],
                    'forecasts'         => $fresh['forecasts'],
                ]
            );
        } catch (\Throwable $e) {
            Log::error("Failed to save historical air‐quality data: {$e->getMessage()}");
        }

        return $fresh;
    }

    protected function formatFromHistorical(
        AirPollutionHistoricalData $hist,
        StationData $st,
        float $lat,
        float $lon
    ): array {
        return [
            'timestamp'      => $hist->created_at->toDateTimeString(),
            'request'        => [
                'latitude'   => $lat,
                'longitude'  => $lon,
            ],
            'station'        => $st->toArray(),
            'airQuality'     => [
                'index'      => $hist->air_quality_index,
                'pollutants' => [
                    'pm10'   => ['value' => $hist->pm10],
                    'pm25'   => ['value' => $hist->pm25],
                    'no2'    => ['value' => $hist->no2],
                    'so2'    => ['value' => $hist->so2],
                    'o3'     => ['value' => $hist->o3],
                    'co'     => ['value' => $hist->co],
                ],
            ],
            'measurements'   => $hist->measurements,
            'forecasts'      => $hist->forecasts,
        ];
    }

    protected function formatFresh(array $data, StationData $st, $lat, $lon): array
    {
        return [
            'timestamp'    => now()->toDateTimeString(),
            'request'      => compact('lat','lon'),
            'station'      => $st->toArray(),
            'airQuality'   => $data['indexInfo'],
            'measurements' => $data['measurements'],
            'forecasts'    => $data['forecasts'],
        ];
    }
}
