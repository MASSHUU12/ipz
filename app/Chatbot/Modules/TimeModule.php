<?php

namespace App\Chatbot\Modules;

use App\Chatbot\ModuleInterface;

class TimeModule implements ModuleInterface
{
    public static function getPatterns(): array
    {
        return [
            [
                "pattern" => "/\\b(?:what time is it|current time|time now|what's the time)\\b/i",
                "responses" => ["The current time is  {user_time} ({user_timezone})"],
                "callback" => null,
                "severity" => "low",
                "priority" => 40,
                "enabled" => true,
            ],
            [
                "pattern" => "/\\b(?:what(?:'s| is) the date|what date is it|today's date)\\b/i",
                "responses" => ["Today's date is {user_date} ({user_timezone})"],
                "callback" => null,
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
}
