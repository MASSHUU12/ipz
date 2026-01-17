<?php

namespace App\Chatbot\Modules;

use App\Chatbot\ModuleInterface;
use App\Http\Requests\MessageChatbotRequest;

class UserPreferencesModule implements ModuleInterface
{
    /**
     * Default temperature threshold value when enabling temperature warnings
     * without specifying a custom threshold.
     */
    private const DEFAULT_TEMPERATURE_THRESHOLD = 10.00;

    /**
     * Get or create user preference for the given user.
     *
     * Returns the existing UserPreference instance for the user, or creates
     * a new one if it doesn't exist. The new instance is not saved to the
     * database until the caller explicitly calls save() on it.
     *
     * @param \App\Models\User $user The user whose preference to retrieve or create
     * @return \App\Models\UserPreference The user's preference instance
     */
    private static function getOrCreatePreference($user): \App\Models\UserPreference
    {
        $preferences = $user->preference;

        if (!$preferences) {
            $preferences = new \App\Models\UserPreference();
            $preferences->user_id = $user->id;
        }

        return $preferences;
    }

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
            [
                "description" => "Changes the user's city preference.",
                "group" => "User",
                "pattern" => "/^(set|change|update)\\s+(my\\s+)?(city|location)\\s+to\\s+(.+)/i",
                "responses" => null,
                "callback" => "setCity",
                "severity" => "medium",
                "priority" => 50,
                "enabled" => true,
                "stop_processing" => true,
                "access_level" => "authenticated",
            ],
            [
                "description" => "Changes the user's notification method.",
                "group" => "User",
                "pattern" => "/^(set|change|update)\\s+(my\\s+)?(notification|notice)\\s+(method|type)\\s+to\\s+(sms|e-?mail|email|both)/i",
                "responses" => null,
                "callback" => "setNotificationMethod",
                "severity" => "medium",
                "priority" => 50,
                "enabled" => true,
                "stop_processing" => true,
                "access_level" => "authenticated",
            ],
            [
                "description" => "Enables or disables warning subscriptions.",
                "group" => "User",
                "pattern" => "/^(enable|disable|turn on|turn off|activate|deactivate)\\s+(meteorological|hydrological|air quality)\\s+warnings?/i",
                "responses" => null,
                "callback" => "toggleWarning",
                "severity" => "medium",
                "priority" => 50,
                "enabled" => true,
                "stop_processing" => true,
                "access_level" => "authenticated",
            ],
            [
                "description" => "Enables or disables temperature warnings with optional threshold.",
                "group" => "User",
                "pattern" => "/^(enable|disable|turn on|turn off|activate|deactivate)\\s+temperature\\s+warnings?(?:\\s+(?:at|with|to)\\s+([0-9]+\\.?[0-9]*))?/i",
                "responses" => null,
                "callback" => "setTemperatureWarning",
                "severity" => "medium",
                "priority" => 50,
                "enabled" => true,
                "stop_processing" => true,
                "access_level" => "authenticated",
            ],
            [
                "description" => "Sets temperature warning threshold.",
                "group" => "User",
                "pattern" => "/^(set|change|update)\\s+temperature\\s+(threshold|limit)\\s+to\\s+([0-9]+\\.?[0-9]*)/i",
                "responses" => null,
                "callback" => "setTemperatureThreshold",
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

    /**
     * Sets or updates the user's city preference.
     *
     * @param array $matches
     * @param MessageChatbotRequest $request
     * @return string
     */
    public static function setCity(array $matches, MessageChatbotRequest $request): string
    {
        $user = $request->user();

        if (!$user) {
            return "I'm sorry, you need to be logged in to change your preferences.";
        }

        $city = isset($matches[4]) ? trim($matches[4]) : null;

        if (!$city) {
            return "Please specify a city name. For example: 'set my city to Warsaw'";
        }

        $preferences = self::getOrCreatePreference($user);
        $preferences->city = $city;
        $preferences->save();

        return sprintf("✅ Your city has been updated to **%s**.", $city);
    }

    /**
     * Sets or updates the user's notification method.
     *
     * @param array $matches
     * @param MessageChatbotRequest $request
     * @return string
     */
    public static function setNotificationMethod(array $matches, MessageChatbotRequest $request): string
    {
        $user = $request->user();

        if (!$user) {
            return "I'm sorry, you need to be logged in to change your preferences.";
        }

        $method = isset($matches[5]) ? strtolower(trim($matches[5])) : null;

        if (!$method) {
            return "Please specify a notification method. Available options: SMS, E-mail, or Both.";
        }

        // Normalize the method
        $normalizedMethod = match ($method) {
            'sms' => 'SMS',
            'email', 'e-mail' => 'E-mail',
            'both' => 'Both',
            default => null,
        };

        if (!$normalizedMethod) {
            return "Invalid notification method. Available options: SMS, E-mail, or Both.";
        }

        $preferences = self::getOrCreatePreference($user);
        $preferences->notice_method = $normalizedMethod;
        $preferences->save();

        return sprintf("✅ Your notification method has been updated to **%s**.", $normalizedMethod);
    }

    /**
     * Toggles warning subscriptions (meteorological, hydrological, air quality).
     *
     * @param array $matches
     * @param MessageChatbotRequest $request
     * @return string
     */
    public static function toggleWarning(array $matches, MessageChatbotRequest $request): string
    {
        $user = $request->user();

        if (!$user) {
            return "I'm sorry, you need to be logged in to change your preferences.";
        }

        $action = isset($matches[1]) ? strtolower(trim($matches[1])) : null;
        $warningType = isset($matches[2]) ? strtolower(trim($matches[2])) : null;

        $enableWarning = in_array($action, ['enable', 'turn on', 'activate']);

        // Map warning type to preference field
        $fieldMap = [
            'meteorological' => 'meteorological_warnings',
            'hydrological' => 'hydrological_warnings',
            'air quality' => 'air_quality_warnings',
        ];

        if (!isset($fieldMap[$warningType])) {
            return "Invalid warning type. Available options: meteorological, hydrological, or air quality.";
        }

        $field = $fieldMap[$warningType];
        $preferences = self::getOrCreatePreference($user);
        $preferences->$field = $enableWarning;
        $preferences->save();

        $status = $enableWarning ? "enabled" : "disabled";
        $warningName = ucfirst($warningType);

        return sprintf("✅ %s warnings have been **%s**.", $warningName, $status);
    }

    /**
     * Enables or disables temperature warnings with optional threshold.
     *
     * @param array $matches
     * @param MessageChatbotRequest $request
     * @return string
     */
    public static function setTemperatureWarning(array $matches, MessageChatbotRequest $request): string
    {
        $user = $request->user();

        if (!$user) {
            return "I'm sorry, you need to be logged in to change your preferences.";
        }

        $action = isset($matches[1]) ? strtolower(trim($matches[1])) : null;
        $threshold = isset($matches[2]) && $matches[2] !== '' ? floatval($matches[2]) : null;

        $enableWarning = in_array($action, ['enable', 'turn on', 'activate']);

        $preferences = self::getOrCreatePreference($user);
        $preferences->temperature_warning = $enableWarning;

        if ($enableWarning && $threshold !== null) {
            $preferences->temperature_check_value = $threshold;
        } elseif ($enableWarning && $preferences->temperature_check_value === null) {
            // Set default threshold if enabling warnings without a threshold and no existing value
            $preferences->temperature_check_value = self::DEFAULT_TEMPERATURE_THRESHOLD;
        }

        $preferences->save();

        if ($enableWarning) {
            $currentThreshold = (float) $preferences->temperature_check_value;
            return sprintf(
                "✅ Temperature warnings have been **enabled** with a threshold of **%.1f°C**.",
                $currentThreshold
            );
        } else {
            return "✅ Temperature warnings have been **disabled**.";
        }
    }

    /**
     * Sets the temperature warning threshold.
     *
     * @param array $matches
     * @param MessageChatbotRequest $request
     * @return string
     */
    public static function setTemperatureThreshold(array $matches, MessageChatbotRequest $request): string
    {
        $user = $request->user();

        if (!$user) {
            return "I'm sorry, you need to be logged in to change your preferences.";
        }

        $threshold = isset($matches[3]) ? floatval($matches[3]) : null;

        if ($threshold === null) {
            return "Please specify a temperature threshold. For example: 'set temperature threshold to 25'";
        }

        $preferences = self::getOrCreatePreference($user);
        $preferences->temperature_check_value = $threshold;
        $preferences->save();

        $warningStatus = $preferences->temperature_warning ? "enabled" : "disabled";

        return sprintf(
            "✅ Temperature threshold has been updated to **%.1f°C**. Temperature warnings are currently **%s**.",
            $threshold,
            $warningStatus
        );
    }
}
