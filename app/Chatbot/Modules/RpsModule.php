<?php

namespace App\Chatbot\Modules;

use App\Chatbot\ModuleInterface;
use App\Http\Requests\MessageChatbotRequest;

class RpsModule implements ModuleInterface
{
    public static function getPatterns(): array
    {
        return [
            [
                "pattern" => "/\\b(?:i choose|play|rps)?\\s*(rock|paper|scissors)\\b/i",
                "responses" => [],
                "callback" => "rpsCallback",
                "severity" => "low",
                "priority" => 35,
                "enabled" => true,
            ],
        ];
    }

    public static function getMTime(): int
    {
        $p = __FILE__;
        return is_file($p) ? (int) @filemtime($p) : 0;
    }

    public static function rpsCallback(array $matches, MessageChatbotRequest $request): string
    {
        $userMove = strtolower($matches[1] ?? "");

        $valid = ["rock", "paper", "scissors"];
        if (!in_array($userMove, $valid, true)) {
            return "I didn't catch your move. Say 'rock', 'paper' or 'scissors'.";
        }

        $botMove = $valid[array_rand($valid)];

        $result = "draw";
        if ($userMove === $botMove) {
            $result = "draw";
        } elseif (
            ($userMove === "rock" && $botMove === "scissors") ||
            ($userMove === "paper" && $botMove === "rock") ||
            ($userMove === "scissors" && $botMove === "paper")
        ) {
            $result = "user";
        } else {
            $result = "bot";
        }

        $emoji = [
            "rock" => "🪨",
            "paper" => "📄",
            "scissors" => "✂️",
        ];

        $outcomeText = match ($result) {
            "user" => "You win! 🎉",
            "bot" => "I win! 🤖",
            "draw" => "It's a draw. 🤝",
        };

        return "You played {$userMove} {$emoji[$userMove]} — I played {$botMove} {$emoji[$botMove]}. {$outcomeText}";
    }
}
