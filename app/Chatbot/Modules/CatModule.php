<?php

namespace App\Chatbot\Modules;

use App\Chatbot\ModuleInterface;
use App\Http\Requests\MessageChatbotRequest;

class CatModule implements ModuleInterface
{
    public static function getPatterns(): array
    {
        return [
            [
                "pattern" => "/\\b(show me a cat|ascii cat|make me an ascii cat)\\b/i",
                "responses" => [],
                "callback" => "asciiCatCallback",
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

    public static function asciiCatCallback(array $matches, MessageChatbotRequest $request): string
    {
        $cat = <<<EOF
        Here you go! 😺

         /\_/\
        ( o.o )
         > ^ <
        EOF;
        return $cat;
    }
}
