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
    private const DEFAULT_FALLBACK_LOCALE = 'en';

    /**
     * Get chatbot patterns from the database, with caching.
     *
     * Patterns returned are scoped to the given user using the Pattern::accessibleTo scope.
     *
     * @param \App\Models\User|null $user
     * @param string $locale
     * @return \Illuminate\Database\Eloquent\Collection
     */
     private function getPatterns(?\App\Models\User $user = null, string $locale = self::DEFAULT_FALLBACK_LOCALE)
     {
        if ($user === null) {
            $cacheKey = "chatbot_patterns:public";
        } elseif ($user->can("Super Admin")) {
            $cacheKey = "chatbot_patterns:super";
        } else {
            $cacheKey = "chatbot_patterns:user:" . $user->id;
        }

        $cacheKey .= ":locale:" . $locale;

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

    private function determineLocale(MessageChatbotRequest $request): string
    {
        $locale = $request->input('locale')
            ?? $request->input('language')
            ?? ($request->user()?->locale ?? null)
            ?? app()->getLocale()
            ?? self::DEFAULT_FALLBACK_LOCALE;

        $locale = strtolower(str_replace('_', '-', $locale));
        if (strpos($locale, '-') !== false) {
            $locale = explode('-', $locale)[0];
        }

        return $locale ?: self::DEFAULT_FALLBACK_LOCALE;
    }

    private function getLocalizedValue($value, string $locale, ?string $fallback = null)
    {
        $fallback = $fallback ?? self::DEFAULT_FALLBACK_LOCALE;

        if (is_string($value) || is_null($value)) {
            return $value;
        }

        if (!is_array($value)) {
            return $value;
        }

        if (array_keys($value) !== range(0, count($value) - 1)) {
            if (array_key_exists($locale, $value)) {
                return $value[$locale];
            }
            if (array_key_exists($fallback, $value)) {
                return $value[$fallback];
            }
            return reset($value);
        }

        return $value;
    }

    private function getPatternForLocale($patternField, string $locale): ?string
    {
        $resolved = $this->getLocalizedValue($patternField, $locale, self::DEFAULT_FALLBACK_LOCALE);

        if (is_array($resolved)) {
            foreach ($resolved as $v) {
                if (is_string($v) && trim($v) !== '') {
                    return $v;
                }
            }
            return null;
        }

        return is_string($resolved) ? $resolved : null;
    }

    private function getResponsesForLocale($responsesField, string $locale): array
    {
        $resolved = $this->getLocalizedValue($responsesField, $locale, self::DEFAULT_FALLBACK_LOCALE);

        if (is_string($resolved)) {
            return [$resolved];
        }

        if (is_array($resolved)) {
            if (array_keys($resolved) !== range(0, count($resolved) - 1)) {
                $out = [];
                foreach ($resolved as $v) {
                    if (is_array($v)) {
                        foreach ($v as $s) {
                            if (is_string($s)) {
                                $out[] = $s;
                            }
                        }
                    } elseif (is_string($v)) {
                        $out[] = $v;
                    }
                }
                return $out;
            }

            return array_values(array_filter($resolved, fn($v) => is_string($v)));
        }

        return [];
    }

    public function message(MessageChatbotRequest $request): JsonResponse
    {
        $locale = $this->determineLocale($request);
        $patterns = $this->getPatterns($request->user(), $locale);

        $question = $request->input("content");
        $answer = "I'm sorry, my ability to respond is limited. Please ask your questions correctly.";
        $payload = null;

        $tz = new DateTimeZone($request->input("timezone") ?? (config("app.timezone") ?? "UTC"));
        $now = new DateTime("now", $tz);
        $data = [
            "user" => $request->user(),
            "bot" => ["name" => self::BOT_NAME],
            "user_timezone" => $tz->getName(),
            "user_date" => $now->format("Y-m-d"),
            "user_time" => $now->format("H:i:s"),
        ];

        foreach ($patterns as $pattern) {
            $patternRegex = $this->getPatternForLocale($pattern->pattern, $locale);

            if (empty($patternRegex)) {
                continue;
            }

            $matchResult = preg_match($patternRegex, $question, $matches);
            if ($matchResult === 1) {
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
                } else {
                    $responses = $this->getResponsesForLocale($pattern->responses, $locale);
                    if (!empty($responses)) {
                        $response = $responses[array_rand($responses)];
                        $payload = $this->getLocalizedValue($pattern->payload ?? null, $locale);
                    }
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
