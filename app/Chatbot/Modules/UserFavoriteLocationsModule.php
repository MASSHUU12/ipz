<?php

namespace App\Chatbot\Modules;

use App\Chatbot\ModuleInterface;
use App\Http\Requests\MessageChatbotRequest;

class UserFavoriteLocationsModule implements ModuleInterface
{
    public static function getPatterns(): array
    {
        return [
            [
                "description" => "Lists the user's favorite locations.",
                "group" => "User",
                "pattern" =>
                    "/^(show|list|display|what are|view)\\s+(my\\s+)?(favorite|favourites|favorites)\\s+(locations|places)/i",
                "responses" => null,
                "callback" => "listFavoriteLocations",
                "severity" => "medium",
                "priority" => 50,
                "enabled" => true,
                "stop_processing" => true,
                "access_level" => "authenticated",
            ],
        ];
    }

    public static function getMTime(): int
    {
        $p = __FILE__;
        return is_file($p) ? (int) @filemtime($p) : 0;
    }

    /**
     * Lists the user's favorite locations.
     *
     * @param array $matches
     * @param MessageChatbotRequest $request
     * @return string
     */
    public static function listFavoriteLocations(array $matches, MessageChatbotRequest $request): string
    {
        $user = $request->user();

        if (!$user) {
            return "You need to be logged in to view your favorite locations.";
        }

        $locations = $user->favoriteLocations;

        if ($locations->isEmpty()) {
            return "You have no favorite locations saved. You can add one by telling me a city or coordinates.";
        }

        $lines = ["Here are your favorite locations:"];

        $i = 1;
        foreach ($locations as $loc) {
            $city = $loc->city ?? "Unnamed";
            $lat = isset($loc->lat) ? rtrim(rtrim(number_format($loc->lat, 6, ".", ""), "0"), ".") : "—";
            $lng = isset($loc->lng) ? rtrim(rtrim(number_format($loc->lng, 6, ".", ""), "0"), ".") : "—";

            $lines[] = sprintf("%d) %s — lat: %s, lng: %s", $i++, $city, $lat, $lng);
        }

        $lines[] = "";
        $lines[] = "To remove or manage locations, say 'remove favorite [city]' or 'add favorite [city]'.";

        return implode("\n", $lines);
    }
}
