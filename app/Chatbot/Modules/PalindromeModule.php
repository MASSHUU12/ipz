<?php

namespace App\Chatbot\Modules;

use App\Chatbot\ModuleInterface;
use App\Http\Requests\MessageChatbotRequest;

if (!function_exists("mb_strrev")) {
    function mb_strrev(string $str): string
    {
        $len = mb_strlen($str);
        $out = "";
        while ($len-- > 0) {
            $out .= mb_substr($str, $len, 1);
        }
        return $out;
    }
}

class PalindromeModule implements ModuleInterface
{
    public static function getPatterns(): array
    {
        return [
            [
                "pattern" => "/\\bis\\s+['\\\"]?(.+?)['\\\"]?\\s+a\\s+palindrome\\??$/i",
                "responses" => [],
                "callback" => "palindromeCallback",
                "severity" => "low",
                "priority" => 45,
                "enabled" => true,
            ],
        ];
    }

    public static function getMTime(): int
    {
        $p = __FILE__;
        return is_file($p) ? (int) @filemtime($p) : 0;
    }

    public static function palindromeCallback(array $matches, MessageChatbotRequest $request): string
    {
        $raw = $matches[1] ?? "";
        $normalized = mb_strtolower(preg_replace("/[^\\p{L}\\p{N}]+/u", "", $raw));
        if ($normalized === "") {
            return "I couldn't find any letters or numbers in that phrase to check.";
        }

        $reversed = mb_strrev($normalized);
        $isPalindrome = $normalized === $reversed;

        return "'" . $raw . "' " . ($isPalindrome ? "is" : "is not") . " a palindrome " . ($isPalindrome ? "✅" : "❌");
    }
}
