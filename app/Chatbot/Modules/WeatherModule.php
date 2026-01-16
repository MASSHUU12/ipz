<?php

namespace App\Chatbot\Modules;

use App\Chatbot\ModuleInterface;
use App\Http\Requests\MessageChatbotRequest;
use App\Models\SynopticHistoricalData;

class WeatherModule implements ModuleInterface
{
    public static function getPatterns(): array
    {
        return [
            [
                "description" => "Shows current weather for a city or station",
                "group" => "Weather",
                "pattern" => "/\\b(?:what(?:'s| is)?|show|check|tell me)(?: the)? (?:current )?weather(?: in| for| at)?\\s+(.+?)(?:\\s+city)?\\s*\\??$/i",
                "responses" => null,
                "callback" => "showWeather",
                "severity" => "medium",
                "priority" => 65,
                "enabled" => true,
                "access_level" => "public",
            ],
            [
                "description" => "Shows temperature for a specific location",
                "group" => "Weather",
                "pattern" => "/\\b(?:what(?:'s| is)?|show|check|tell me)(?: the)? temperature(?: in| for| at)?\\s+(.+?)(?:\\s+city)?\\s*\\??$/i",
                "responses" => null,
                "callback" => "showTemperature",
                "severity" => "medium",
                "priority" => 62,
                "enabled" => true,
                "access_level" => "public",
            ],
            [
                "description" => "General weather inquiry",
                "group" => "Weather",
                "pattern" => "/\\b(?:how(?:'s| is)?)(?: the)? weather\\b/i",
                "responses" => [
                    "I can tell you about the weather! Please specify a city, for example: 'What's the weather in Warsaw?'",
                    "To check the weather, please tell me which city you're interested in."
                ],
                "callback" => null,
                "severity" => "low",
                "priority" => 30,
                "enabled" => true,
                "access_level" => "public",
            ],
        ];
    }

    public static function getMTime(): int
    {
        $p = __FILE__;
        return is_file($p) ? (int) @filemtime($p) : 0;
    }

    /**
     * Shows current weather for a city or station.
     *
     * @param array $matches
     * @param MessageChatbotRequest $request
     * @return string
     */
    public static function showWeather(array $matches, MessageChatbotRequest $request): string
    {
        $location = isset($matches[1]) ? trim($matches[1]) : null;

        if (!$location) {
            return "Please specify a city or station name. For example: 'What's the weather in Krakow?'";
        }

        // If location contains a comma (e.g., user pasted an address), drop everything after it
        if (strpos($location, ',') !== false) {
            $location = trim(substr($location, 0, strpos($location, ',')));
        }

        // Find the latest weather data for this location
        $weather = SynopticHistoricalData::whereRaw('LOWER(station_name) LIKE ?', ['%' . strtolower($location) . '%'])
            ->orderByDesc('measurement_date')
            ->orderByDesc('measurement_hour')
            ->first();

        if (!$weather) {
            return sprintf(
                "I couldn't find weather data for '%s'. Please check the spelling or try another city.",
                $location
            );
        }

        $lines = [];
        $lines[] = sprintf("Weather for %s:", $weather->station_name);
        $lines[] = "";

        if ($weather->temperature !== null) {
            $lines[] = sprintf("Temperature: %.1f°C", $weather->temperature);
        }

        if ($weather->wind_speed !== null) {
            $windDesc = self::getWindDescription($weather->wind_speed);
            $lines[] = sprintf("Wind: %d km/h (%s)", $weather->wind_speed, $windDesc);
            
            if ($weather->wind_direction !== null) {
                $direction = self::getWindDirection($weather->wind_direction);
                $lines[] = sprintf("Wind Direction: %s (%d°)", $direction, $weather->wind_direction);
            }
        }

        if ($weather->relative_humidity !== null) {
            $lines[] = sprintf("Humidity: %.0f%%", $weather->relative_humidity);
        }

        if ($weather->pressure !== null) {
            $lines[] = sprintf("Pressure: %.1f hPa", $weather->pressure);
        }

        if ($weather->rainfall_total !== null && $weather->rainfall_total > 0) {
            $lines[] = sprintf("Rainfall: %.1f mm", $weather->rainfall_total);
        }

        $lines[] = "";
        $measurementTime = $weather->measurement_date->format('Y-m-d') . ' ' . sprintf('%02d:00', $weather->measurement_hour);
        $lines[] = "Measured at: " . $measurementTime;

        return implode("\n", $lines);
    }

    /**
     * Shows temperature for a specific location.
     *
     * @param array $matches
     * @param MessageChatbotRequest $request
     * @return string
     */
    public static function showTemperature(array $matches, MessageChatbotRequest $request): string
    {
        $location = isset($matches[1]) ? trim($matches[1]) : null;

        if (!$location) {
            return "Please specify a city or station name. For example: 'What's the temperature in Gdansk?'";
        }

        // If location contains a comma, drop everything after it
        if (strpos($location, ',') !== false) {
            $location = trim(substr($location, 0, strpos($location, ',')));
        }

        // Find the latest weather data for this location
        $weather = SynopticHistoricalData::whereRaw('LOWER(station_name) LIKE ?', ['%' . strtolower($location) . '%'])
            ->orderByDesc('measurement_date')
            ->orderByDesc('measurement_hour')
            ->first();

        if (!$weather) {
            return sprintf(
                "I couldn't find temperature data for '%s'. Please check the spelling or try another city.",
                $location
            );
        }

        if ($weather->temperature === null) {
            return sprintf("Temperature data is not available for %s at the moment.", $weather->station_name);
        }

        $measurementTime = $weather->measurement_date->format('Y-m-d') . ' ' . sprintf('%02d:00', $weather->measurement_hour);
        
        return sprintf(
            "The temperature in %s is %.1f°C (measured at %s).",
            $weather->station_name,
            $weather->temperature,
            $measurementTime
        );
    }

    /**
     * Get wind direction from degrees.
     *
     * @param int $degrees
     * @return string
     */
    private static function getWindDirection(int $degrees): string
    {
        $directions = ['N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE', 'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'];
        $index = round($degrees / 22.5) % 16;
        return $directions[$index];
    }

    /**
     * Get wind speed description.
     *
     * @param int $speed
     * @return string
     */
    private static function getWindDescription(int $speed): string
    {
        if ($speed < 1) return 'Calm';
        if ($speed < 6) return 'Light air';
        if ($speed < 12) return 'Light breeze';
        if ($speed < 20) return 'Gentle breeze';
        if ($speed < 29) return 'Moderate breeze';
        if ($speed < 39) return 'Fresh breeze';
        if ($speed < 50) return 'Strong breeze';
        if ($speed < 62) return 'Near gale';
        if ($speed < 75) return 'Gale';
        if ($speed < 89) return 'Strong gale';
        if ($speed < 103) return 'Storm';
        if ($speed < 118) return 'Violent storm';
        return 'Hurricane';
    }
}
