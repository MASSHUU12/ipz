<?php

namespace App\Http\Controllers;

use App\Http\Requests\MessageChatbotRequest;
use App\Models\Pattern;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

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
            $cacheKey = 'chatbot_patterns:public';
        } elseif ($user->can('Super Admin')) {
            $cacheKey = 'chatbot_patterns:super';
        } else {
            $cacheKey = 'chatbot_patterns:authenticated';
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
                ->where('enabled', true)
                ->orderByRaw($severityOrder . ' DESC')
                ->orderBy('priority', 'desc')
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

        if (strpos($callbackString, '::') !== false) {
            [$class, $method] = explode('::', $callbackString, 2);
            if (class_exists($class) && method_exists($class, $method)) {
                return [$class, $method];
            }
        }

        return null;
    }

    public function message(MessageChatbotRequest $request): JsonResponse
    {
        $patterns = $this->getPatterns($request->user());
        $question = $request->input('content');
        $answer = "I'm sorry, my ability to respond is limited. Please ask your questions correctly.";

        foreach ($patterns as $pattern) {
            if (@preg_match($pattern->pattern, $question, $matches)) {
                $callback = $this->resolveCallback($pattern->callback);

                if ($callback) {
                    try {
                        $result = call_user_func($callback, $matches, $request);
                        if (is_string($result) && $result !== '') {
                            $answer = str_replace('ChatBot', self::BOT_NAME, $result);
                            $pattern->increment('hit_count');
                            $pattern->forceFill(['last_used_at' => now()])->save();
                        }
                    } catch (Exception $e) {
                        report($e);
                        $answer = "Sorry, I couldn't process that request right now.";
                    }
                } elseif (!empty($pattern->responses)) {
                    $response = $pattern->responses[array_rand($pattern->responses)];
                    if (strpos($response, '%1') !== false && isset($matches[1])) {
                        $response = str_replace('%1', $matches[1], $response);
                    }
                    $answer = str_replace('ChatBot', self::BOT_NAME, $response);
                    $pattern->increment('hit_count');
                    $pattern->forceFill(['last_used_at' => now()])->save();
                }

                if ($pattern->stop_processing) {
                    break;
                }
            }
        }

        return response()->json([
            'question' => $question,
            'answer' => $answer,
        ]);
    }
}
