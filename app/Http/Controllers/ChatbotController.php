<?php

namespace App\Http\Controllers;

use App\Chatbot\ModuleLoader;
use App\Http\Requests\MessageChatbotRequest;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ChatbotController extends Controller
{
    private const BOT_NAME = "Marian";
    private const PATTERNS_FILE = "chatbot_patterns.json";

    private static $PATTERNS = null;
    private static $PATTERNS_MTIME = 0;

    /**
     * Load patterns from the patterns file AND from modules.
     * Modules live in app/Chatbot/Modules and implement App\Chatbot\ModuleInterface.
     */
    private static function ensurePatternsLoaded(): void
    {
        try {
            $disk = Storage::disk("local");
        } catch (Exception $e) {
            self::$PATTERNS ??= [];
            return;
        }

        $fileExists = false;
        try {
            $fileExists = $disk->exists(self::PATTERNS_FILE);
        } catch (Exception $e) {
            $fileExists = false;
        }

        $fileMtime = 0;
        $fileData = [];
        if ($fileExists) {
            try {
                $fileMtime = (int) $disk->lastModified(self::PATTERNS_FILE);
            } catch (Exception $e) {
                $fileMtime = 0;
            }

            try {
                $json = $disk->get(self::PATTERNS_FILE);
                $decoded = @json_decode($json, true);
                if (is_array($decoded)) {
                    $fileData = $decoded;
                }
            } catch (Exception $e) {
                $fileData = [];
            }
        }

        $modules = ModuleLoader::loadModules();
        $modulePatterns = $modules["patterns"] ?? [];
        $modulesMtime = $modules["mtime"] ?? 0;

        $globalMtime = max($fileMtime, $modulesMtime);

        if (self::$PATTERNS !== null && $globalMtime <= self::$PATTERNS_MTIME) {
            return;
        }

        $raw = array_merge($fileData, $modulePatterns);

        $normalized = [];
        foreach ($raw as $e) {
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

            if (is_string($callback)) {
                if (strpos($callback, "::") !== false) {
                    [$class, $method] = explode("::", $callback, 2);
                    if (class_exists($class) && method_exists($class, $method)) {
                        $callback = [$class, $method];
                    } else {
                        $callback = null;
                    }
                } elseif (method_exists(self::class, $callback)) {
                    $callback = [self::class, $callback];
                } else {
                    $callback = null;
                }
            } elseif (is_array($callback) && is_callable($callback)) {
                // Ok
            } elseif ($callback instanceof \Closure) {
                // Ok
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
        self::$PATTERNS_MTIME = $globalMtime;
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
}
