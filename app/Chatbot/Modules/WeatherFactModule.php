<?php

namespace App\Chatbot\Modules;

use App\Chatbot\ModuleInterface;
use App\Http\Requests\MessageChatbotRequest;

class WeatherFactModule implements ModuleInterface
{
    private static array $facts = [
        "A lightning bolt can be 5 times hotter than the surface of the Sun.",
        "Raindrops are not tear-shaped but rather flattened spheres.",
        "Antarctica is the coldest, windiest and... driest place on Earth.",
        "Clouds can weigh up to a million tons.",
        "A rainbow is actually a full circle, but from the ground we only see an arc.",
        "In 1816 there was no summer because of the eruption of Mount Tambora.",
        "Snow is not white – it is transparent; the whiteness is due to light reflection.",
        "The strongest wind recorded on Earth was 408 km/h (Cyclone Olivia).",
        "Venezuela has a place where storms occur about 140–160 nights a year (Catatumbo lightning).",
        "Frogs can freeze in winter and 'come back to life' in spring."
    ];

    public static function getPatterns(): array
    {
        return [
            [
                "pattern" => "/\\b(fact|fact of the day|fun fact)\\b/i",
                "responses" => [],
                "callback" => "getFactCallback",
                "severity" => "low",
                "priority" => 20,
                "enabled" => true,
            ],
        ];
    }

    public static function getMTime(): int
    {
        $p = __FILE__;
        return is_file($p) ? (int) @filemtime($p) : 0;
    }

    public static function getFactCallback(array $matches, MessageChatbotRequest $request): ?array
    {
        if (empty(self::$facts)) {
            return [
                'answer' => "I don't have any facts today."
            ];
        }

        $lastIndex = session('last_weather_fact_index', -1);
        $availableIndexes = array_keys(self::$facts);

        if (count($availableIndexes) > 1 && $lastIndex !== -1) {
            $availableIndexes = array_diff($availableIndexes, [$lastIndex]);
        }

        $randomIndex = $availableIndexes[array_rand($availableIndexes)];

        session(['last_weather_fact_index' => $randomIndex]);

        $fact = self::$facts[$randomIndex];
        $answer = "Weather fact: " . $fact;

        $response = [
            'answer' => $answer
        ];

        if (rand(1, 100) <= 5) {
            $response['payload'] = [
                'type' => 'image_url',
                'url' => 'https://cataas.com/cat',
                'alt' => 'Losowe zdjęcie kota'
            ];
        }

        return $response;
    }
}
