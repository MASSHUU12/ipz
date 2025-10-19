<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageChatbotRequest;
use DateTime;
use DateTimeZone;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

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

class ChatbotController extends Controller
{
    private const BOT_NAME = "Marian";
    private const PATTERNS_FILE = "chatbot_patterns.json";

    /**
     * Patterns are loaded from the JSON file at runtime in an array-of-entries schema:
     * [
     *   {
     *     "pattern": "/.../i",
     *     "responses": ["..."],
     *     "callback": "methodName",
     *     "severity": "high",
     *     "priority": 10,
     *     "enabled": true
     *   },
     *   ...
     * ]
     */
    private static $PATTERNS = null;
    private static $PATTERNS_MTIME = 0;

    private static function ensurePatternsLoaded(): void
    {
        try {
            $disk = Storage::disk("local");
        } catch (Exception $e) {
            self::$PATTERNS ??= [];
            return;
        }

        $exists = false;
        try {
            $exists = $disk->exists(self::PATTERNS_FILE);
        } catch (Exception $e) {
            $exists = false;
        }

        if (!$exists) {
            self::$PATTERNS ??= [];
            self::$PATTERNS_MTIME = 0;
            return;
        }

        $mtime = 0;
        try {
            $mtime = (int) $disk->lastModified(self::PATTERNS_FILE);
        } catch (Exception $e) {
            $mtime = 0;
        }

        if (self::$PATTERNS !== null && $mtime <= self::$PATTERNS_MTIME) {
            return;
        }

        // Read file
        $json = "";
        try {
            $json = $disk->get(self::PATTERNS_FILE);
        } catch (Exception $e) {
            self::$PATTERNS = [];
            self::$PATTERNS_MTIME = $mtime;
            return;
        }

        $data = @json_decode($json, true);
        if (!is_array($data)) {
            self::$PATTERNS = [];
            self::$PATTERNS_MTIME = $mtime;
            return;
        }

        $normalized = [];
        foreach ($data as $e) {
            if (!is_array($e) || empty($e["pattern"])) {
                continue;
            }

            $pattern = $e["pattern"];

            $responses = $e["responses"] ?? ($e["response"] ?? []);
            if (is_string($responses)) {
                $responses = [$responses];
            } elseif (!is_array($responses)) {
                $responses = [];
            }

            $callback = $e["callback"] ?? null;
            if (is_string($callback) && method_exists(self::class, $callback)) {
                $callback = [self::class, $callback];
            } elseif (is_array($callback) && is_callable($callback)) {
                // Keep as-is if already callable-like
            } else {
                $callback = null;
            }

            $severity = $e["severity"] ?? "low";
            $priority = isset($e["priority"]) ? (int) $e["priority"] : 0;
            $enabled = isset($e["enabled"]) ? (bool) $e["enabled"] : true;

            if (!$enabled) {
                continue;
            }
            if (empty($responses) && $callback === null) {
                continue;
            }

            $normalized[] = [
                "pattern" => $pattern,
                "responses" => $responses,
                "callback" => $callback,
                "severity" => $severity,
                "priority" => $priority,
                "enabled" => $enabled,
            ];
        }

        // Sort: severity weight (desc) then priority (desc)
        $severityWeight = ["critical" => 100, "high" => 75, "medium" => 50, "low" => 25];
        usort($normalized, function ($a, $b) use ($severityWeight) {
            $sa = $severityWeight[strtolower($a["severity"])] ?? 0;
            $sb = $severityWeight[strtolower($b["severity"])] ?? 0;
            if ($sa !== $sb) {
                return $sb <=> $sa; // Higher severity first
            }
            return $b["priority"] <=> $a["priority"]; // Higher priority first
        });

        self::$PATTERNS = $normalized;
        self::$PATTERNS_MTIME = $mtime;
    }

    public function message(MessageChatbotRequest $request): JsonResponse
    {
        self::ensurePatternsLoaded();

        $question = $request["content"];
        $answer = "I'm sorry, my ability to respond is limited. Please ask your questions correctly.";

        foreach (self::$PATTERNS as $entry) {
            if (empty($entry["enabled"])) {
                continue;
            }

            $pattern = $entry["pattern"];
            if (@preg_match($pattern, $question, $matches)) {
                if (isset($entry["callback"]) && is_callable($entry["callback"])) {
                    try {
                        $result = call_user_func($entry["callback"], $matches, $request);
                        if (is_string($result) && $result !== "") {
                            $answer = str_replace("ChatBot", self::BOT_NAME, $result);
                        }
                    } catch (Exception $e) {
                        $answer = "Sorry, I couldn't process that request right now.";
                    }
                } else {
                    $responses = $entry["responses"] ?? [];
                    if (!is_array($responses)) {
                        $responses = [$responses];
                    }
                    if (!empty($responses)) {
                        $response = $responses[array_rand($responses)];
                        if (strpos($response, "%1") !== false && isset($matches[1])) {
                            $response = str_replace("%1", $matches[1], $response);
                        }
                        $answer = str_replace("ChatBot", self::BOT_NAME, $response);
                    }
                }
                break;
            }
        }

        return response()->json([
            "question" => $question,
            "answer" => $answer,
        ]);
    }

    protected static function timeCallback(array $matches, $request): string
    {
        $tz = $request->input("timezone") ?? (config("app.timezone") ?? "UTC");

        try {
            $zone = new DateTimeZone($tz);
        } catch (Exception $e) {
            $zone = new DateTimeZone("UTC");
            $tz = "UTC";
        }

        $dt = new DateTime("now", $zone);
        return "The current time is " . $dt->format("H:i:s") . " (" . $tz . ").";
    }

    protected static function dateCallback(array $matches, $request): string
    {
        $tz = $request->input("timezone") ?? (config("app.timezone") ?? "UTC");
        try {
            $zone = new DateTimeZone($tz);
        } catch (Exception $e) {
            $zone = new DateTimeZone("UTC");
            $tz = "UTC";
        }

        $dt = new DateTime("now", $zone);
        return "Today's date is " . $dt->format("Y-m-d") . " (" . $tz . ").";
    }

    protected static function rpsCallback(array $matches, MessageChatbotRequest $request): string
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

    protected static function asciiCatCallback(array $matches, MessageChatbotRequest $request): string
    {
        $cat =
            <<<EOF
            Here you go! 😺\n
            EOF
            .
            " /\\_/\\\n" .
            "( o.o )\n" .
            " > ^ <";
        return $cat;
    }

    protected static function palindromeCallback(array $matches, MessageChatbotRequest $request): string
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

    protected static function movieQuoteCallback(array $matches, MessageChatbotRequest $request): string
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
