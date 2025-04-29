<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AirPollutionHistoricalData;
use App\Services\GiosApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AirQualityController extends Controller
{
    protected $giosApi;

    const STATION_CACHE_TTL = 1440;   // minutes
    const AIR_QUALITY_CACHE_TTL = 30; // minutes
    const SENSOR_CACHE_TTL = 60;      // minutes
    const FORECAST_CACHE_TTL = 60;    // minutes

    /**
     * Constructor
     *
     * @param GiosApi $giosApi
     */
    public function __construct(GiosApi $giosApi)
    {
        $this->giosApi = $giosApi;
    }

    /**
     * Get air quality data for a specific location
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAirQuality(Request $request): JsonResponse
    {
        $validator = $request->validate([
            'lat' => 'required|numeric|between:49.0,55.0', // Poland's latitude range
            'lon' => 'required|numeric|between:14.0,24.0', // Poland's longitude range
        ]);

        try {
            $latitude = $request->input('lat');
            $longitude = $request->input('lon');

            // Round coords for cache/key consistency
            $cacheCoords = [round($latitude, 3), round($longitude, 3)];

            // Find nearest station (cache hot)
            $stationCacheKey = 'station_' . implode('_', $cacheCoords);
            $nearestStation = Cache::remember(
                $stationCacheKey,
                self::STATION_CACHE_TTL * 60,
                function () use ($latitude, $longitude) {
                    return $this->giosApi->findNearestStation($latitude, $longitude);
                }
            );

            if (!$nearestStation) {
                return response()->json([
                    'success' => false,
                    'message' => 'No measuring stations found'
                ], 404);
            }

            $stationId = $nearestStation['id'];
            $stationName = $nearestStation['stationName'];
            $distance = GiosApi::calculateDistance(
                $latitude,
                $longitude,
                (float)$nearestStation['gegrLat'],
                (float)$nearestStation['gegrLon']
            );

            // Check historical data first
            $historical = AirPollutionHistoricalData::where('station_id', $stationId)
                ->latest('created_at')
                ->first();

            if ($historical && $historical->created_at->greaterThan(now()->subMinutes(self::AIR_QUALITY_CACHE_TTL))) {
                return response()->json([
                    'success' => true,
                    'timestamp' => $historical->created_at->toDateTimeString(),
                    'request' => [
                        'latitude'  => $latitude,
                        'longitude' => $longitude,
                    ],
                    'data' => [
                        'station' => [
                            'id'        => $stationId,
                            'name'      => $stationName,
                            'latitude'  => $nearestStation['gegrLat'],
                            'longitude' => $nearestStation['gegrLon'],
                            'distance'  => round($distance, 2) . ' km',
                            'address'   => $nearestStation['addressStreet'] ?? null,
                            'city'      => $nearestStation['city']['name'] ?? null,
                            'commune'   => $nearestStation['city']['commune']['communeName'] ?? null,
                            'district'  => $nearestStation['city']['commune']['districtName'] ?? null,
                            'province'  => $nearestStation['city']['commune']['provinceName'] ?? null,
                        ],
                        'airQuality'  => [
                            'index'      => $historical->air_quality_index,
                            'pollutants' => [
                                'pm10' => ['value' => $historical->pm10],
                                'pm25' => ['value' => $historical->pm25],
                                'no2'  => ['value' => $historical->no2],
                                'so2'  => ['value' => $historical->so2],
                                'o3'   => ['value' => $historical->o3],
                                'co'   => ['value' => $historical->co],
                            ],
                        ],
                        'measurements' => $historical->measurements,
                        'forecasts'    => $historical->forecasts,
                    ],
                ]);
            }

            // No recent history -> fall back to cache / API

            // Cache air quality index by station ID
            $aqCacheKey = 'air_quality_index_' . $stationId;
            $airQualityIndex = Cache::remember(
                $aqCacheKey,
                self::AIR_QUALITY_CACHE_TTL * 60,
                fn() => $this->giosApi->getAirQualityIndex($stationId)
            );

            // Cache station sensors by station ID
            $sensorsCacheKey = 'station_sensors_' . $stationId;
            $sensors = Cache::remember(
                $sensorsCacheKey,
                self::STATION_CACHE_TTL * 60,
                fn() => $this->giosApi->getStationSensors($stationId)
            );

            $measurementData = [];
            if ($sensors) {
                foreach ($sensors as $sensor) {
                    $sensorId = $sensor['id'];

                    // Cache sensor data by sensor ID
                    $sensorDataCacheKey = 'sensor_data_' . $sensorId;
                    $sensorData = Cache::remember(
                        $sensorDataCacheKey,
                        self::SENSOR_CACHE_TTL * 60,
                        fn() => $this->giosApi->getSensorData($sensorId)
                    );

                    if ($sensorData && !empty($sensorData['values'])) {
                        $latestValue = null;
                        $measurementTime = null;

                        foreach ($sensorData['values'] as $value) {
                            if ($value['value'] !== null) {
                                $latestValue = $value['value'];
                                $measurementTime = $value['date'];
                                break;
                            }
                        }

                        if ($latestValue !== null) {
                            $measurementData[] = [
                                'parameter' => $sensor['param']['paramName'],
                                'code' => $sensor['param']['paramCode'],
                                'value' => $latestValue,
                                'unit' => 'µg/m³',
                                'measurementTime' => $measurementTime
                            ];
                        }
                    }
                }
            }

            // Get forecasts if possible
            $forecasts = [];
            if (isset($nearestStation['city']['id'])) {
                $terytCodes = [
                    $nearestStation['city']['id'],
                    '1465' // Default to Warsaw if the city ID doesn't work
                ];

                foreach (['PM10', 'NO2', 'SO2', 'O3'] as $pollutant) {
                    foreach ($terytCodes as $terytCode) {
                        // Cache forecast by pollutant and TERYT code
                        $forecastCacheKey = 'forecast_' . $pollutant . '_' . $terytCode;
                        $forecast = Cache::remember(
                            $forecastCacheKey,
                            self::FORECAST_CACHE_TTL * 60,
                            fn() => $this->giosApi->getForecast($pollutant, $terytCode)
                        );

                        if ($forecast) {
                            $forecasts[$pollutant] = $forecast;
                            break 2;
                        }
                    }
                }
            }

            // Prepare air quality information
            $airQualityInfo = null;
            if ($airQualityIndex) {
                $airQualityInfo = [
                    'index'           => $airQualityIndex['stIndexLevel']['indexLevelName'] ?? 'No data',
                    'calculationTime' => $airQualityIndex['stCalcDate'] ?? null,
                    'sourceDataTime'  => $airQualityIndex['stSourceDataDate'] ?? null,
                ];
                foreach (['pm10', 'pm25', 'o3', 'no2', 'so2', 'co'] as $p) {
                    $idxKey = "{$p}IndexLevel";
                    $dtKey  = "{$p}CalcDate";

                    if (isset($airQualityIndex[$idxKey])) {
                        $airQualityInfo['pollutants'][$p] = [
                            'index'           => $airQualityIndex[$idxKey]['indexLevelName'] ?? 'No data',
                            'calculationTime' => $airQualityIndex[$dtKey] ?? null,
                        ];
                    }
                }
            }

            // Persist to historical DB
            try {
                $getVal = fn($code) => (optional(
                    collect($measurementData)->firstWhere('code', $code)
                )['value']) ?? null;

                AirPollutionHistoricalData::updateOrCreate(
                    [
                        'station_id'        => $stationId,
                        'latitude'          => $cacheCoords[0],
                        'longitude'         => $cacheCoords[1],
                        'station_name'      => $stationName,
                        'air_quality_index' => $airQualityInfo['index'] ?? null,
                        'pm10'              => $getVal('PM10'),
                        'pm25'              => $getVal('PM2.5'),
                        'no2'               => $getVal('NO2'),
                        'so2'               => $getVal('SO2'),
                        'o3'                => $getVal('O3'),
                        'co'                => $getVal('CO'),
                        'measurements'      => $measurementData,
                        'forecasts'         => $forecasts,
                    ]
                );
            } catch (\Exception $e) {
                Log::error('Failed to save historical data: ' . $e->getMessage());
            }

            $response = [
                'success' => true,
                'timestamp' => date('Y-m-d H:i:s'),
                'request' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude
                ],
                'data' => [
                    'station' => [
                        'id' => $stationId,
                        'name' => $stationName,
                        'latitude' => $nearestStation['gegrLat'],
                        'longitude' => $nearestStation['gegrLon'],
                        'distance' => round($distance, 2) . ' km',
                        'address' => $nearestStation['addressStreet'] ?? null,
                        'city' => $nearestStation['city']['name'] ?? null,
                        'commune' => $nearestStation['city']['commune']['communeName'] ?? null,
                        'district' => $nearestStation['city']['commune']['districtName'] ?? null,
                        'province' => $nearestStation['city']['commune']['provinceName'] ?? null
                    ],
                    'airQuality' => $airQualityInfo,
                    'measurements' => $measurementData,
                    'forecasts' => $forecasts
                ],
            ];

            return response()->json($response);
        } catch (\Exception $e) {
            Log::error('Air Quality API error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve air quality data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
