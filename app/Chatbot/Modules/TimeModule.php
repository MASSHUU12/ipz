<?php

namespace App\Chatbot\Modules;

use App\Chatbot\ModuleInterface;
use App\Http\Requests\MessageChatbotRequest;
use DateTime;
use DateTimeZone;

class TimeModule implements ModuleInterface
{
    public static function getPatterns(): array
    {
        return [
            [
                "pattern" => "/\\b(?:what time is it|current time|time now|what's the time)\\b/i",
                "responses" => [],
                "callback" => "timeCallback",
                "severity" => "low",
                "priority" => 40,
                "enabled" => true,
            ],
            [
                "pattern" => "/\\b(?:what(?:'s| is) the date|what date is it|today's date)\\b/i",
                "responses" => [],
                "callback" => "dateCallback",
                "severity" => "low",
                "priority" => 40,
                "enabled" => true,
            ],
        ];
    }

    public static function getMTime(): int
    {
        $p = __FILE__;
        return is_file($p) ? (int) @filemtime($p) : 0;
    }

    public static function timeCallback(array $matches, MessageChatbotRequest $request): string
    {
        $tz = $matches[1] ?? ($request->input("timezone") ?? (config("app.timezone") ?? "UTC"));
        try {
            $zone = new DateTimeZone($tz);
        } catch (\Exception $e) {
            $zone = new DateTimeZone("UTC");
            $tz = "UTC";
        }
        $dt = new DateTime("now", $zone);
        return "The current time is " . $dt->format("H:i:s") . " ({$tz}).";
    }

    public static function dateCallback(array $matches, $request): string
    {
        $tz = $matches[1] ?? ($request->input("timezone") ?? (config("app.timezone") ?? "UTC"));
        try {
            $zone = new DateTimeZone($tz);
        } catch (\Exception $e) {
            $zone = new DateTimeZone("UTC");
            $tz = "UTC";
        }
        $dt = new DateTime("now", $zone);
        return "Today's date is " . $dt->format("Y-m-d") . " ({$tz}).";
    }
}
