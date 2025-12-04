<?php

namespace App\Chatbot\Modules;

use App\Chatbot\ModuleInterface;
use App\Http\Requests\MessageChatbotRequest;

class PageFinderModule implements ModuleInterface
{
    private static array $pages = [
        'login' => ['url' => '/login', 'name' => 'Login'],
        'dashboard' => ['url' => '/dashboard', 'name' => 'Dashboard'],
        'my profile' => ['url' => '/profile', 'name' => 'Your Profile'],
        'profile' => ['url' => '/profile', 'name' => 'Your Profile'],
        'leaderboard' => ['url' => '/leaderboard', 'name' => 'Leaderboard'],
        'register' => ['url' => '/register', 'name' => 'Register'],
        'settings' => ['url' => '/edit-profile', 'name' => 'Settings'],
        'alert rcb' => ['url' => 'https://www.gov.pl/web/rcb/alertrcbb', 'name' => 'Alert RCB'],
    ];

    public static function getPatterns(): array
    {
        $pageKeywords = implode('|', array_keys(self::$pages));

        return [
            [
                "pattern" => "/\\b(where is|where can i find|go to|show me|take me to) (the |a |an )?({$pageKeywords})\\b/i",
                "responses" => [],
                "callback" => "findPageCallback",
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
        $pageKeyword = strtolower(trim($matches[count($matches) - 1]));

        if (isset(self::$pages[$pageKeyword])) {
            $page = self::$pages[$pageKeyword];

            $isExternal = filter_var($page['url'], FILTER_VALIDATE_URL);
            $finalUrl = $isExternal ? $page['url'] : url($page['url']);

            return [
                'answer' => "Sure! You can find the {$page['name']} page here.",
                'payload' => [
                    'type' => 'navigation_link',
                    'url' => $finalUrl,
                    'text' => "Go to {$page['name']}",
                ],
            ];
        }

        return null;
    }
}
