<?php

namespace App\Chatbot\Modules;

use App\Chatbot\ModuleInterface;
use App\Http\Requests\MessageChatbotRequest;

class MovieModule implements ModuleInterface
{
    public static function getPatterns(): array
    {
        return [
            [
                "pattern" =>
                    "/\\b(i'll be back|may the force be with you|you talking to me|here's looking at you)\\b/i",
                "responses" => [],
                "callback" => "movieQuoteCallback",
                "severity" => "low",
                "priority" => 22,
                "enabled" => true,
            ],
        ];
    }

    public static function getMTime(): int
    {
        $p = __FILE__;
        return is_file($p) ? (int) @filemtime($p) : 0;
    }

    public static function movieQuoteCallback(array $matches, MessageChatbotRequest $request): string
    {
        $trigger = mb_strtolower($matches[1] ?? "");

        $map = [
            "i'll be back" => "Hasta la vista, baby. 😎",
            "may the force be with you" => "And also with you. ✨",
            "you talking to me" => "Well, are you? 😏",
            "here's looking at you" => "Kid. — Casablanca reference. 🍸",
        ];

        foreach ($map as $k => $v) {
            if (mb_stripos($trigger, $k) !== false) {
                return $v;
            }
        }

        return "That sounded familiar… but I don't have the line ready. Try another classic!";
    }
}
