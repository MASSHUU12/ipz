<?php

namespace App\Chatbot\Modules;

use App\Chatbot\ModuleInterface;
use App\Http\Requests\MessageChatbotRequest;

class PageFinderModule implements ModuleInterface
{
    private static array $pages = [
        'login' => ['url' => '/login', 'name' => ['en' => 'Login', 'pl' => 'Logowanie']],
        'dashboard' => ['url' => '/dashboard', 'name' => ['en' => 'Dashboard', 'pl' => 'Panel']],
        'my profile' => ['url' => '/profile', 'name' => ['en' => 'Your Profile', 'pl' => 'Twój profil']],
        'profile' => ['url' => '/profile', 'name' => ['en' => 'Your Profile', 'pl' => 'Twój profil']],
        'leaderboard' => ['url' => '/leaderboard', 'name' => ['en' => 'Leaderboard', 'pl' => 'Ranking']],
        'register' => ['url' => '/register', 'name' => ['en' => 'Register', 'pl' => 'Rejestracja']],
        'settings' => ['url' => '/edit-profile', 'name' => ['en' => 'Settings', 'pl' => 'Ustawienia']],
        'alert rcb' => ['url' => 'https://www.gov.pl/web/rcb/alertrcbb', 'name' => ['en' => 'Alert RCB', 'pl' => 'Alert RCB']],
    ];

    public static function getPatterns(): array
    {
        $pageKeywords = implode('|', array_keys(self::$pages));

        return [
            [
                "pattern" => [
                    "en" => "/\\b(where is|where can i find|go to|show me|take me to) (the |a |an )?({$pageKeywords})\\b/i",
                    "pl" => "/\\b(?:gdzie (?:znajd(?:z|z)e?\\w*|mog(?:ę|e) znaleź(?:ć|c)|jest)|poka[zż] mi|przej[dź] do) (?:strona |)?({$pageKeywords})\\b/iu",
                ],
                "responses" => [
                    "en" => ["Sure! You can find the %1 page here.", "Here is the link to %1."],
                    "pl" => ["Jasne! Stronę %1 znajdziesz tutaj.", "Oto link do %1."],
                ],
                "callback" => \App\Chatbot\Modules\PageFinderModule::class . "::findPageCallback",
                "severity" => "medium",
                "priority" => 30,
                "enabled" => true,
            ],
        ];
    }

    public static function getMTime(): int
    {
        $p = __FILE__;
        return is_file($p) ? (int) @filemtime($p) : 0;
    }

    public static function findPageCallback(array $matches, MessageChatbotRequest $request): ?array
    {
        $locale = $request->input('locale') ?? app()->getLocale() ?? 'en';
        $locale = strtolower(explode('-', str_replace('_', '-', $locale))[0]);

        $pageKeyword = strtolower(trim($matches[count($matches) - 1]));

        if (isset(self::$pages[$pageKeyword])) {
            $page = self::$pages[$pageKeyword];

            $name = is_array($page['name']) ? ($page['name'][$locale] ?? $page['name']['en'] ?? array_values($page['name'])[0]) : $page['name'];

            $isExternal = filter_var($page['url'], FILTER_VALIDATE_URL);
            $finalUrl = $isExternal ? $page['url'] : url($page['url']);

            $answers = [
                'en' => "Sure! You can find the {$name} page here.",
                'pl' => "Jasne! Stronę {$name} znajdziesz tutaj.",
            ];

            $answer = $answers[$locale] ?? $answers['en'];

            return [
                'answer' => $answer,
                'payload' => [
                    'type' => 'navigation_link',
                    'url' => $finalUrl,
                    'text' => $locale === 'pl' ? "Przejdź do {$name}" : "Go to {$name}",
                ],
            ];
        }

        return null;
    }
}
