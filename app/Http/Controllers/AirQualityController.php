<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\GiosApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AirQualityController extends Controller
{
    protected $giosApi;

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
            $nearestStation = $this->giosApi->findNearestStation($latitude, $longitude);

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

            $airQualityIndex = $this->giosApi->getAirQualityIndex($stationId);
            $sensors = $this->giosApi->getStationSensors($stationId);
            $measurementData = [];

            if ($sensors) {
                foreach ($sensors as $sensor) {
                    $sensorData = $this->giosApi->getSensorData($sensor['id']);

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
                        $forecast = $this->giosApi->getForecast($pollutant, $terytCode);

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
