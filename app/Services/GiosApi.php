<?php

namespace App\Services;

use Exception;

/**
* GIOŚ Air Quality API Client
*
* A PHP class for interacting with the air quality API provided by the
* Chief Inspectorate of Environmental Protection (GIOŚ) in Poland.
* https://powietrze.gios.gov.pl/pjp/content/api
*/
class GiosApi
{
    /** @var string Base URL for the GIOŚ API */
    private const GIOS_API_URL = 'https://api.gios.gov.pl/pjp-api/rest';

    /** @var string Base URL for the forecast API by Institute of Environmental Protection */
    private const FORECAST_API_URL = 'https://api.prognozy.ios.edu.pl/v1';

    /** @var int Request timeout in seconds */
    private $timeout = 10;

    public function setTimeout(int $seconds): void
    {
        $this->timeout = $seconds;
    }

    public function getAllStations(): ?array
    {
        return $this->makeRequest(self::GIOS_API_URL . '/station/findAll');
    }

    public function getStationSensors(int $stationId): ?array
    {
        return $this->makeRequest(self::GIOS_API_URL . '/station/sensors/' . $stationId);
    }

    public function getSensorData(int $sensorId): ?array
    {
        return $this->makeRequest(self::GIOS_API_URL . '/data/getData/' . $sensorId);
    }

    public function getAirQualityIndex(int $stationId): ?array
    {
        return $this->makeRequest(self::GIOS_API_URL . '/aqindex/getIndex/' . $stationId);
    }

    /**
    * Get air quality forecast for a specific pollutant and region
    *
    * @param string $pollutantCode Pollutant code (PM10, NO2, SO2, O3)
    * @param string $terytCode TERYT code for the region
    * @return array|null Forecast data or null on error
    */
    public function getForecast(string $pollutantCode, string $terytCode): ?array
    {
        return $this->makeRequest(self::FORECAST_API_URL . '/' . $pollutantCode . '/' . $terytCode);
    }

    /**
    * Find nearest station based on coordinates
    *
    * @param float $latitude Latitude
    * @param float $longitude Longitude
    * @return array|null Nearest station or null on error
    */
    public function findNearestStation(float $latitude, float $longitude): ?array
    {
        $stations = $this->getAllStations();

        if (!$stations) {
            return null;
        }

        $nearestStation = null;
        $shortestDistance = PHP_FLOAT_MAX;

        foreach ($stations as $station) {
            if (empty($station['gegrLat']) || empty($station['gegrLon'])) {
                continue;
            }

            $distance = self::calculateDistance(
                $latitude,
                $longitude,
                (float)$station['gegrLat'],
                (float)$station['gegrLon']
            );

            if ($distance < $shortestDistance) {
                $shortestDistance = $distance;
                $nearestStation = $station;
            }
        }

        return $nearestStation;
    }

    /**
    * Calculate distance between two geographical points using Haversine formula
    *
    * @param float $lat1 Latitude of first point
    * @param float $lon1 Longitude of first point
    * @param float $lat2 Latitude of second point
    * @param float $lon2 Longitude of second point
    * @return float Distance in kilometers
    */
    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        return $distance;
    }

    /**
    * Make an HTTP request to the API
    *
    * @param string $url API URL
    * @return array|null JSON response as array or null on error
    */
    private function makeRequest(string $url): ?array
    {
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
            curl_setopt($ch, CURLOPT_USERAGENT, 'GiosAPI PHP Client/1.0');

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            if ($httpCode !== 200 || $response === false) {
                return null;
            }

            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }

            return $data;
        } catch (Exception $e) {
            return null;
        }
    }
}
