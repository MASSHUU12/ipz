<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array|null getAllStations()
 * @method static array|null getStationSensors(int $stationId)
 * @method static array|null getSensorData(int $sensorId)
 * @method static array|null getAirQualityIndex(int $stationId)
 * @method static array|null getForecast(string $pollutantCode, string $terytCode)
 * @method static array|null findNearestStation(float $latitude, float $longitude)
 * @method static void setTimeout(int $seconds)
 *
 * @see \App\Services\GiosApi
 */
class Gios extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\GiosApi::class;
    }
}
