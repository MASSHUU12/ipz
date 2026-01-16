<?php

namespace App\Chatbot\Modules;

use App\Chatbot\ModuleInterface;
use App\Http\Requests\MessageChatbotRequest;
use App\Models\AirPollutionLeaderboard;

class LeaderboardModule implements ModuleInterface
{
    public static function getPatterns(): array
    {
        return [
            [
                "description" => "Shows the top cities with worst air pollution",
                "group" => "Leaderboard",
                "pattern" => "/\\b(?:show|display|list|what(?:'s| is)?)(?: the)?(?: top| worst)?(?: air)?(?: pollution)? leaderboard\\b/i",
                "responses" => null,
                "callback" => "showLeaderboard",
                "severity" => "medium",
                "priority" => 60,
                "enabled" => true,
                "access_level" => "public",
            ],
            [
                "description" => "Shows air pollution rank for a specific city",
                "group" => "Leaderboard",
                "pattern" => "/\\b(?:what(?:'s| is)?|show|check)(?: the)? (?:air )?(?:pollution )?(?:rank|position|place)(?: for| of| in)\\s+(.+?)(?:\\s+city)?\\s*\\??$/i",
                "responses" => null,
                "callback" => "showCityRank",
                "severity" => "medium",
                "priority" => 55,
                "enabled" => true,
                "access_level" => "public",
            ],
            [
                "description" => "Shows the most polluted cities",
                "group" => "Leaderboard",
                "pattern" => "/\\b(?:which|what) (?:cities|city) (?:have|has)(?: the)? (?:worst|highest|most) (?:air )?pollution\\b/i",
                "responses" => null,
                "callback" => "showLeaderboard",
                "severity" => "medium",
                "priority" => 58,
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
     * Shows the top cities with worst air pollution.
     *
     * @param array $matches
     * @param MessageChatbotRequest $request
     * @return string
     */
    public static function showLeaderboard(array $matches, MessageChatbotRequest $request): string
    {
        $topEntries = AirPollutionLeaderboard::orderBy('air_quality_index', 'desc')
            ->limit(5)
            ->get();

        if ($topEntries->isEmpty()) {
            return "No leaderboard data is currently available. Please try again later.";
        }

        $lines = ["Here are the top 5 cities with the worst air pollution:"];
        $lines[] = "";

        $rank = 1;
        foreach ($topEntries as $entry) {
            $city = $entry->city ?? $entry->station_name ?? "Unknown";
            $aqi = $entry->air_quality_index ?? "N/A";
            $pm10 = $entry->pm10 ? number_format($entry->pm10, 1) : "N/A";
            $pm25 = $entry->pm25 ? number_format($entry->pm25, 1) : "N/A";

            $lines[] = sprintf(
                "%d. %s - AQI: %s (PM10: %s, PM2.5: %s)",
                $rank++,
                $city,
                $aqi,
                $pm10,
                $pm25
            );
        }

        $timestamp = $topEntries->first()->timestamp ?? $topEntries->first()->updated_at;
        if ($timestamp) {
            $lines[] = "";
            $lines[] = "Last updated: " . $timestamp->format('Y-m-d H:i');
        }

        return implode("\n", $lines);
    }

    /**
     * Shows the air pollution rank for a specific city.
     *
     * @param array $matches
     * @param MessageChatbotRequest $request
     * @return string
     */
    public static function showCityRank(array $matches, MessageChatbotRequest $request): string
    {
        $cityName = isset($matches[1]) ? trim($matches[1]) : null;

        if (!$cityName) {
            return "Please specify a city name. For example: 'What is the pollution rank for Warsaw?'";
        }

        // Normalize city name for case-insensitive search
        $entry = AirPollutionLeaderboard::whereRaw('LOWER(city) LIKE ?', [strtolower($cityName) . '%'])
            ->orWhereRaw('LOWER(station_name) LIKE ?', [strtolower($cityName) . '%'])
            ->orderBy('air_quality_index', 'desc')
            ->first();

        if (!$entry) {
            return sprintf(
                "I couldn't find air pollution data for '%s'. Please check the spelling or try another city.",
                $cityName
            );
        }

        // Calculate rank
        $rank = AirPollutionLeaderboard::where('air_quality_index', '>', $entry->air_quality_index)
            ->count() + 1;

        $total = AirPollutionLeaderboard::count();

        $city = $entry->city ?? $entry->station_name ?? "Unknown";
        $aqi = $entry->air_quality_index ?? "N/A";
        $pm10 = $entry->pm10 ? number_format($entry->pm10, 1) : "N/A";
        $pm25 = $entry->pm25 ? number_format($entry->pm25, 1) : "N/A";

        $lines = [
            sprintf("%s is ranked #%d out of %d cities.", $city, $rank, $total),
            "",
            sprintf("Air Quality Index: %s", $aqi),
            sprintf("PM10: %s μg/m³", $pm10),
            sprintf("PM2.5: %s μg/m³", $pm25),
        ];

        $timestamp = $entry->timestamp ?? $entry->updated_at;
        if ($timestamp) {
            $lines[] = "";
            $lines[] = "Last updated: " . $timestamp->format('Y-m-d H:i');
        }

        return implode("\n", $lines);
    }
}
