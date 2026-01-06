<?php

namespace App\Chatbot\Modules;

use App\Chatbot\ModuleInterface;
use App\Http\Requests\MessageChatbotRequest;

class LanguageModule implements ModuleInterface
{
    public static function getPatterns(): array
    {
        return [
            [
                "pattern" => [
                    "en" => "/\\b(?:change language to|set language to|switch to|language)\\s+(polish|english|pl|en)\\b/i",
                    "pl" => "/\\b(?:zmień język na|ustaw język na|przełącz na|język)\\s+(polski|angielski|pl|en)\\b/i",
                ],
                "responses" => [],
                "callback" => "changeLanguageCallback",
                "severity" => "high",
                "priority" => 95,
                "enabled" => true,
                "stop_processing" => true,
                "access_level" => "public",
                "description" => "Language change command",
                "group" => "language",
            ],
        ];
    }

    public static function getMTime(): int
    {
        $p = __FILE__;
        return is_file($p) ? (int) @filemtime($p) : 0;
    }

    public static function changeLanguageCallback(array $matches, MessageChatbotRequest $request): array
    {
        $language = strtolower($matches[1] ?? '');
        
        // Map language names to locale codes
        $localeMap = [
            'polish' => 'pl',
            'polski' => 'pl',
            'pl' => 'pl',
            'english' => 'en',
            'angielski' => 'en',
            'en' => 'en',
        ];
        
        $locale = $localeMap[$language] ?? 'en';
        
        // Update user preference if user is authenticated
        $user = $request->user();
        if ($user && $user->preference) {
            $user->preference->update(['locale' => $locale]);
        }
        
        // Return localized response
        $responses = [
            'en' => [
                'Language changed to English. How can I help you?',
                'Switched to English. What can I do for you?'
            ],
            'pl' => [
                'Język zmieniony na Polski. Jak mogę Ci pomóc?',
                'Przełączono na Polski. Co mogę dla Ciebie zrobić?'
            ]
        ];
        
        $responseList = $responses[$locale] ?? $responses['en'];
        $answer = $responseList[array_rand($responseList)];
        
        return [
            'answer' => $answer,
            'payload' => [
                'action' => 'language_changed',
                'locale' => $locale,
            ]
        ];
    }
}
