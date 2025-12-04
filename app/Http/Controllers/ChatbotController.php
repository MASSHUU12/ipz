<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageChatbotRequest;
use App\Models\Pattern;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use DateTime;
use DateTimeZone;

class ChatbotController extends Controller
{
    private const BOT_NAME = "Marian";

    /**
     * Get chatbot patterns from the database, with caching.
     *
     * Patterns returned are scoped to the given user using the Pattern::accessibleTo scope.
     *
     * @param \App\Models\User|null $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getPatterns(?\App\Models\User $user = null)
    {
        if ($user === null) {
            $cacheKey = "chatbot_patterns:public";
        } elseif ($user->can("Super Admin")) {
            $cacheKey = "chatbot_patterns:super";
        } else {
            $cacheKey = "chatbot_patterns:user:" . $user->id;
        }

        return Cache::remember($cacheKey, 3600, function () use ($user) {
            $severityOrder = "CASE
                WHEN severity = 'critical' THEN 100
                WHEN severity = 'high' THEN 75
                WHEN severity = 'medium' THEN 50
                WHEN severity = 'low' THEN 25
                ELSE 0
            END";

            return Pattern::accessibleTo($user)
                ->where("enabled", true)
                ->orderByRaw($severityOrder . " DESC")
                ->orderBy("priority", "desc")
                ->get();
        });
    }

    /**
     * Resolve a callback string from the database into a valid PHP callable.
     *
     * @param string|null $callbackString
     * @return callable|null
     */
    private function resolveCallback(?string $callbackString): ?callable
    {
        if (empty($callbackString)) {
            return null;
        }

        if (strpos($callbackString, "::") !== false) {
            [$class, $method] = explode("::", $callbackString, 2);
            if (class_exists($class) && method_exists($class, $method)) {
                return [$class, $method];
            }
        }

        return null;
    }

    /**
     * Replace placeholders in a response string with dynamic data, with fallback support.
     *
     * @param string $response
     * @param array $data
     * @return string
     */
    private function hydrateResponse(string $response, array $data): string
    {
        $pattern = '/\{([a-zA-Z0-9_.]+)(?:\s*\?\?\s*(?:`([^`]*)`|\'([^\']*)\'|"([^"]*)"))?\}/';

        return preg_replace_callback(
            $pattern,
            function ($matches) use ($data) {
                $value = $this->getValue($data, $matches[1]);

                if ($value !== null) {
                    return is_scalar($value) ? (string) $value : $matches[0];
                }

                if (isset($matches[2]) && $matches[2] !== "") {
                    return $matches[2];
                }
                if (isset($matches[3]) && $matches[3] !== "") {
                    return $matches[3];
                }
                if (isset($matches[4]) && $matches[4] !== "") {
                    return $matches[4];
                }

                return $matches[0];
            },
            $response,
        );
    }

    /**
     * Safely get a value from a nested array or object using dot notation.
     *
     * @param array $data
     * @param string $key
     * @return mixed
     */
    private function getValue(array $data, string $key)
    {
        foreach (explode(".", $key) as $segment) {
            if (is_array($data) && array_key_exists($segment, $data)) {
                $data = $data[$segment];
            } elseif (is_object($data) && isset($data->{$segment})) {
                $data = $data->{$segment};
            } else {
                return null;
            }
        }
        return $data;
    }

    public function message(MessageChatbotRequest $request): JsonResponse
    {
        $patterns = $this->getPatterns($request->user());
        $question = $request->input("content");
        $answer = "I'm sorry, my ability to respond is limited. Please ask your questions correctly.";

        $tz = new DateTimeZone($request->input("timezone") ?? (config("app.timezone") ?? "UTC"));
        $now = new DateTime("now", $tz);
        $data = [
            "user" => $request->user(),
            "bot" => ["name" => self::BOT_NAME],
            "user_timezone" => $tz->getName(),
            "user_date" => $now->format("Y-m-d"),
            "user_time" => $now->format("H:i:s"),
        ];
        $payload = null;

        foreach ($patterns as $pattern) {
            if (@preg_match($pattern->pattern, $question, $matches)) {
                $response = null;
                $callback = $this->resolveCallback($pattern->callback);

                if ($callback) {
                    try {
                        $result = call_user_func($callback, $matches, $request);
                        if (is_string($result) && $result !== "") {
                            $response = $result;
                        } elseif (is_array($result)) {
                            $response = $result['answer'] ?? null;
                            $payload = $result['payload'] ?? null;
                        }
                    } catch (Exception $e) {
                        report($e);
                        $answer = "Sorry, I couldn't process that request right now.";
                    }
                } elseif (!empty($pattern->responses)) {
                    $response = $pattern->responses[array_rand($pattern->responses)];
                    $payload = $pattern->payload ?? null;
                }

                if ($response !== null) {
                    if (strpos($response, "%1") !== false && isset($matches[1])) {
                        $response = str_replace("%1", $matches[1], $response);
                    }
                    $answer = $this->hydrateResponse($response, $data);
                    $pattern->increment("hit_count");
                    $pattern->forceFill(["last_used_at" => now()])->save();
                }

                if ($pattern->stop_processing) {
                    break;
                }
            }
        }

        return response()->json([
            "question" => $question,
            "answer" => $answer,
            "payload" => $payload,
        ]);
    }
}
