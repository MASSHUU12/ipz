<?php

namespace App\Chatbot\Modules;

use App\Chatbot\ModuleInterface;
use App\Http\Requests\MessageChatbotRequest;

class UserPreferencesModule implements ModuleInterface
{
    public static function getPatterns(): array
    {
        return [
            [
                "description" => "Lists the user's notification preferences from their profile.",
                "group" => "User",
                "pattern" => "/^(show|list|view|what are|display)\\s+(my\\s+)?(preferences|settings)/i",
                "responses" => null,
                "callback" => "listPreferences",
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
     * Lists the user's notification preferences.
     *
     * @param array $matches
     * @param MessageChatbotRequest $request
     * @return string
     */
    public static function listPreferences(array $matches, MessageChatbotRequest $request): string
    {
        $user = $request->user();

        if (!$user) {
            return "I'm sorry, you need to be logged in to view your preferences.";
        }

        $preferences = $user->preference;

        if (!$preferences) {
            return "You haven't set up any preferences yet. You can start by telling me your city.";
        }

        $responseLines = ["Here are your current preferences:"];
        $responseLines[] = sprintf("- City: **%s**", $preferences->city ?? "Not set");
        $responseLines[] = sprintf("- Notification Method: **%s**", $preferences->notice_method ?? "Not set");

        $responseLines[] = "\n**Warning Subscriptions:**";
        $responseLines[] =
            "- Meteorological: " . ($preferences->meteorological_warnings ? "✅ Enabled" : "❌ Disabled");
        $responseLines[] = "- Hydrological: " . ($preferences->hydrological_warnings ? "✅ Enabled" : "❌ Disabled");
        $responseLines[] = "- Air Quality: " . ($preferences->air_quality_warnings ? "✅ Enabled" : "❌ Disabled");

        if ($preferences->temperature_warning) {
            $responseLines[] = sprintf(
                "- Temperature: ✅ Enabled (Threshold: **%.1f°C**)",
                $preferences->temperature_check_value,
            );
        } else {
            $responseLines[] = "- Temperature: ❌ Disabled";
        }

        return implode("\n", $responseLines);
    }
}
