<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GiosApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AirQualityController extends Controller
{
    protected $giosApi;

    const STATION_CACHE_TTL = 1440;   // 24 hours for station data
    const AIR_QUALITY_CACHE_TTL = 30; // 30 minutes for air quality data
    const SENSOR_CACHE_TTL = 60;      // 1 hour for sensor data
    const FORECAST_CACHE_TTL = 60;    // 1 hour for forecast data

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

            $cacheCoords = [round($latitude, 3), round($longitude, 3)];
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

            // Cache air quality index by station ID
            $airQualityIndexCacheKey = 'air_quality_index_' . $stationId;
            $airQualityIndex = Cache::remember(
                $airQualityIndexCacheKey,
                self::AIR_QUALITY_CACHE_TTL * 60,
                function () use ($stationId) {
                    return $this->giosApi->getAirQualityIndex($stationId);
                }
            );

            // Cache station sensors by station ID
            $sensorsCacheKey = 'station_sensors_' . $stationId;
            $sensors = Cache::remember(
                $sensorsCacheKey,
                self::STATION_CACHE_TTL * 60,
                function () use ($stationId) {
                    return $this->giosApi->getStationSensors($stationId);
                }
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
                        function () use ($sensorId) {
                            return $this->giosApi->getSensorData($sensorId);
                        }
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

            // Get forecasts if possible (try to extract TERYT code from city property)
            $forecasts = [];
            if (isset($nearestStation['city']['id'])) {
                $terytCodes = [
                    $nearestStation['city']['id'], // Try using city ID as TERYT
                    '1465' // Default to Warsaw if the city ID doesn't work
                ];

                foreach (['PM10', 'NO2', 'SO2', 'O3'] as $pollutant) {
                    foreach ($terytCodes as $terytCode) {
                        // Cache forecast by pollutant and TERYT code
                        $forecastCacheKey = 'forecast_' . $pollutant . '_' . $terytCode;
                        $forecast = Cache::remember(
                            $forecastCacheKey,
                            self::FORECAST_CACHE_TTL * 60,
                            function () use ($pollutant, $terytCode) {
                                return $this->giosApi->getForecast($pollutant, $terytCode);
                            }
                        );

                        if ($forecast) {
                            $forecasts[$pollutant] = $forecast;
                            break;
                        }
                    }
                }
            }

            // Prepare air quality information
            $airQualityInfo = null;
            if ($airQualityIndex) {
                $airQualityInfo = [
                    'index' => isset($airQualityIndex['stIndexLevel']) ?
                        $airQualityIndex['stIndexLevel']['indexLevelName'] : 'No data',
                    'calculationTime' => $airQualityIndex['stCalcDate'] ?? null,
                    'sourceDataTime' => $airQualityIndex['stSourceDataDate'] ?? null,
                ];

                foreach (['pm10', 'pm25', 'o3', 'no2', 'so2', 'co'] as $pollutant) {
                    $indexKey = $pollutant . 'IndexLevel';
                    $calcDateKey = $pollutant . 'CalcDate';

                    if (isset($airQualityIndex[$indexKey]) && is_array($airQualityIndex[$indexKey])) {
                        $airQualityInfo['pollutants'][$pollutant] = [
                            'index' => $airQualityIndex[$indexKey]['indexLevelName'] ?? 'No data',
                            'calculationTime' => $airQualityIndex[$calcDateKey] ?? null
                        ];
                    }
                }
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
