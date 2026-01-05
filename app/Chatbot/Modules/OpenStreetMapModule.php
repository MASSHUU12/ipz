<?php

namespace App\Chatbot\Modules;

use App\Chatbot\ModuleInterface;
use App\Http\Requests\MessageChatbotRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class OpenStreetMapModule implements ModuleInterface
{
    public static function getPatterns(): array
    {
        return [
            [
                "description" => "Show map for coordinates (latitude, longitude)",
                "group" => "Map",
                "pattern" => "/(?:show|display|map|directions?|locate|find).*?(-?\\d+\\.\\d+)\\s*,\\s*(-?\\d+\\.\\d+)/i",
                "responses" => null,
                "callback" => "coordinatesCallback",
                "severity" => "medium",
                "priority" => 60,
                "enabled" => true,
                "stop_processing" => false,
                "access_level" => "public",
            ],
            [
                "description" => "Show map for address or city name",
                "group" => "Map",
                "pattern" => "/(?:show|display|map|directions?|locate|find)\\s+(?:map\\s+)?(?:of\\s+|to\\s+|for\\s+)?([a-zA-ZąćęłńóśźżĄĆĘŁŃÓŚŹŻ\\s-]+?)(?:\\s+on\\s+map|\\.?$)/i",
                "responses" => null,
                "callback" => "addressCallback",
                "severity" => "medium",
                "priority" => 55,
                "enabled" => true,
                "stop_processing" => false,
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
     * Handle coordinate-based map requests.
     *
     * @param array $matches
     * @param MessageChatbotRequest $request
     * @return array
     */
    public static function coordinatesCallback(array $matches, MessageChatbotRequest $request): array
    {
        $lat = (float) $matches[1];
        $lng = (float) $matches[2];

        // Validate coordinates
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            return [
                'answer' => "The coordinates you provided are invalid. Latitude must be between -90 and 90, and longitude must be between -180 and 180.",
                'payload' => null,
            ];
        }

        // Try to get location name from reverse geocoding
        $locationName = self::reverseGeocode($lat, $lng);

        $answer = $locationName 
            ? "Here's the map for {$locationName} (coordinates: {$lat}, {$lng})."
            : "Here's the map for coordinates {$lat}, {$lng}.";

        return [
            'answer' => $answer,
            'payload' => [
                'type' => 'map',
                'lat' => $lat,
                'lng' => $lng,
                'label' => $locationName ?? "Location",
                'zoom' => 13,
            ],
        ];
    }

    /**
     * Handle address-based map requests.
     *
     * @param array $matches
     * @param MessageChatbotRequest $request
     * @return array
     */
    public static function addressCallback(array $matches, MessageChatbotRequest $request): array
    {
        $address = trim($matches[1]);
        
        // Filter out common words that shouldn't be treated as addresses
        $excludedWords = ['me', 'it', 'this', 'that', 'time', 'date', 'weather', 'help'];
        if (in_array(strtolower($address), $excludedWords)) {
            return [
                'answer' => "I couldn't find a location for '{$address}'. Please provide a valid city name or address.",
                'payload' => null,
            ];
        }

        // First, try to find in the database (Polish cities)
        $dbCity = DB::table('air_pollution_leaderboard')
            ->select('city', 'lat', 'lng')
            ->whereRaw('LOWER(city) LIKE ?', ['%' . strtolower($address) . '%'])
            ->first();

        if ($dbCity && isset($dbCity->lat, $dbCity->lng)) {
            return [
                'answer' => "Here's the map for {$dbCity->city} (coordinates: {$dbCity->lat}, {$dbCity->lng}).",
                'payload' => [
                    'type' => 'map',
                    'lat' => (float) $dbCity->lat,
                    'lng' => (float) $dbCity->lng,
                    'label' => $dbCity->city,
                    'zoom' => 13,
                ],
            ];
        }

        // If not found in database, try Nominatim geocoding
        $geocodeResult = self::geocodeAddress($address);
        
        if ($geocodeResult) {
            return [
                'answer' => "Here's the map for {$geocodeResult['display_name']} (coordinates: {$geocodeResult['lat']}, {$geocodeResult['lng']}).",
                'payload' => [
                    'type' => 'map',
                    'lat' => $geocodeResult['lat'],
                    'lng' => $geocodeResult['lng'],
                    'label' => $geocodeResult['display_name'],
                    'zoom' => 13,
                ],
            ];
        }

        return [
            'answer' => "I couldn't find the location '{$address}'. Please check the spelling or try a different location.",
            'payload' => null,
        ];
    }

    /**
     * Geocode an address using OpenStreetMap Nominatim API.
     *
     * @param string $address
     * @return array|null
     */
    private static function geocodeAddress(string $address): ?array
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'IPZ Air Pollution App/1.0',
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $address,
                    'format' => 'json',
                    'limit' => 1,
                    'addressdetails' => 1,
                ]);

            if ($response->successful() && !empty($response->json())) {
                $result = $response->json()[0];
                return [
                    'lat' => (float) $result['lat'],
                    'lng' => (float) $result['lon'],
                    'display_name' => $result['display_name'] ?? $address,
                ];
            }
        } catch (\Exception $e) {
            // Log error but don't expose it to user
            report($e);
        }

        return null;
    }

    /**
     * Reverse geocode coordinates to get a location name.
     *
     * @param float $lat
     * @param float $lng
     * @return string|null
     */
    private static function reverseGeocode(float $lat, float $lng): ?string
    {
        try {
            $response = Http::timeout(5)
                ->withHeaders([
                    'User-Agent' => 'IPZ Air Pollution App/1.0',
                ])
                ->get('https://nominatim.openstreetmap.org/reverse', [
                    'lat' => $lat,
                    'lon' => $lng,
                    'format' => 'json',
                    'addressdetails' => 1,
                ]);

            if ($response->successful() && !empty($response->json())) {
                $result = $response->json();
                return $result['display_name'] ?? null;
            }
        } catch (\Exception $e) {
            // Log error but don't expose it to user
            report($e);
        }

        return null;
    }
}
