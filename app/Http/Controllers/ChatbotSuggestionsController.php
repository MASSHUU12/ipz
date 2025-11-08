<?php

namespace App\Http\Controllers;

use App\Http\Requests\GetChatbotSuggestionsRequest;
use App\Models\Pattern;
use Symfony\Component\HttpFoundation\JsonResponse;

class ChatbotSuggestionsController extends Controller
{
    public function suggest(GetChatbotSuggestionsRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $limit = $validated['limit'] ?? 10;

        $patterns = Pattern::accessibleTo($request->user())
            ->where('enabled', true)->get();
        $suggestions = $patterns->sortByDesc('hit_count')->take($limit);

        $formattedSuggestions = $suggestions->map(function ($pattern) {
            return [
                'suggestion' => $pattern->readable_pattern,
                'description' => $pattern->description,
            ];
        });

        return response()->json($formattedSuggestions->values());
    }
}
