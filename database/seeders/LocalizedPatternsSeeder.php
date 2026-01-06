<?php

namespace Database\Seeders;

use App\Models\Pattern;
use Illuminate\Database\Seeder;

class LocalizedPatternsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder adds Polish and English translations to patterns.
     * It supports both legacy string patterns and new localized patterns.
     */
    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command->error('Refusing to run seeder in production environment.');
            return;
        }

        // Create localized patterns that support both PL and EN
        $localizedPatterns = [
            [
                'description' => 'Language change command',
                'group' => 'language',
                'pattern' => [
                    'en' => '/\\b(?:change language to|set language to|switch to|language)\\s+(polish|english|pl|en)\\b/i',
                    'pl' => '/\\b(?:zmień język na|ustaw język na|przełącz na|język)\\s+(polski|angielski|pl|en)\\b/iu',
                ],
                'responses' => [
                    'en' => [
                        'Language changed to %1. How can I help you?',
                        'Switched to %1. What can I do for you?'
                    ],
                    'pl' => [
                        'Język zmieniony na %1. Jak mogę Ci pomóc?',
                        'Przełączono na %1. Co mogę dla Ciebie zrobić?'
                    ]
                ],
                'payload' => [
                    'en' => ['action' => 'language_changed', 'locale' => 'en'],
                    'pl' => ['action' => 'language_changed', 'locale' => 'pl']
                ],
                'callback' => null,
                'severity' => 'high',
                'priority' => 95,
                'enabled' => true,
                'stop_processing' => true,
                'access_level' => 'public',
            ],
            [
                'description' => 'Greeting - Hello (localized)',
                'group' => 'greetings',
                'pattern' => [
                    'en' => '/\\b(?:hi|hello|hey|hiya|greetings|yo)\\b/i',
                    'pl' => '/\\b(?:cześć|czesc|hej|witaj|witam|siema|dzień dobry|dzien dobry|hejo)\\b/iu',
                ],
                'responses' => [
                    'en' => [
                        'Hello! How can I help you today?',
                        'Hi there! How may I assist you?',
                        'Hey! What can I do for you?'
                    ],
                    'pl' => [
                        'Cześć! Jak mogę Ci dzisiaj pomóc?',
                        'Witaj! W czym mogę Ci pomóc?',
                        'Hej! Co mogę dla Ciebie zrobić?'
                    ]
                ],
                'callback' => null,
                'severity' => 'low',
                'priority' => 20,
                'enabled' => true,
                'stop_processing' => false,
                'access_level' => 'public',
            ],
            [
                'description' => 'Thanks (localized)',
                'group' => 'greetings',
                'pattern' => [
                    'en' => '/\\b(?:thanks|thank you|thx|thankyou)\\b/i',
                    'pl' => '/\\b(?:dzięki|dzieki|dziękuję|dziekuje|thx|dzięks|dzieks)\\b/iu',
                ],
                'responses' => [
                    'en' => ['You\'re welcome!', 'No problem — happy to help!', 'Anytime!'],
                    'pl' => ['Nie ma za co!', 'Nie ma problemu — miło pomóc!', 'Zawsze do usług!']
                ],
                'callback' => null,
                'severity' => 'low',
                'priority' => 15,
                'enabled' => true,
                'stop_processing' => false,
                'access_level' => 'public',
            ],
            [
                'description' => 'Goodbye (localized)',
                'group' => 'greetings',
                'pattern' => [
                    'en' => '/\\b(?:bye|goodbye|see you|see ya|talk to you later)\\b/i',
                    'pl' => '/\\b(?:pa|papa|do widzenia|cześć|czesc|do zobaczenia|na razie|nara)\\b/iu',
                ],
                'responses' => [
                    'en' => ['Goodbye! Have a great day!', 'See you later! If you need anything else, just ask.'],
                    'pl' => ['Do widzenia! Miłego dnia!', 'Na razie! Jeśli będziesz czegoś potrzebować, po prostu zapytaj.']
                ],
                'callback' => null,
                'severity' => 'low',
                'priority' => 15,
                'enabled' => true,
                'stop_processing' => false,
                'access_level' => 'public',
            ],
            [
                'description' => 'Help request (localized)',
                'group' => 'help',
                'pattern' => [
                    'en' => '/\\b(?:help|support|assist|assistance)\\b/i',
                    'pl' => '/\\b(?:pomoc|pomocy|pomóż|pomoz|wsparcie|asyst)\\b/iu',
                ],
                'responses' => [
                    'en' => ['Sure — what do you need help with?', 'I\'m here to help. Please tell me more about the issue.'],
                    'pl' => ['Jasne — w czym potrzebujesz pomocy?', 'Jestem tu, aby pomóc. Powiedz mi więcej o problemie.']
                ],
                'callback' => null,
                'severity' => 'high',
                'priority' => 80,
                'enabled' => true,
                'stop_processing' => false,
                'access_level' => 'public',
            ],
            [
                'description' => 'Error reporting (localized)',
                'group' => 'technical',
                'pattern' => [
                    'en' => '/\\b(?:error|bug|doesn\'t work|does not work|failed|exception|page not found|404)\\b/i',
                    'pl' => '/\\b(?:błąd|blad|bug|nie działa|nie dziala|nie dzia|nie zadziałało|nie zadzialalo|failure|wyjątek|wyjatek|strona nie znaleziona|404)\\b/iu',
                ],
                'responses' => [
                    'en' => [
                        'I\'m sorry you\'re seeing an error. Can you describe what you were doing and paste any error message?',
                        'Thanks for reporting this — please tell me the steps to reproduce and any error text so I can escalate to engineering.'
                    ],
                    'pl' => [
                        'Przykro mi, że widzisz błąd. Czy możesz opisać, co robiłeś i wkleić komunikat o błędzie?',
                        'Dziękuję za zgłoszenie — proszę opisz kroki do odtworzenia i treść błędu, abym mógł przekazać to zespołowi.'
                    ]
                ],
                'callback' => null,
                'severity' => 'high',
                'priority' => 85,
                'enabled' => true,
                'stop_processing' => false,
                'access_level' => 'public',
            ],
        ];

        $this->command->info('Creating ' . count($localizedPatterns) . ' localized patterns...');

        $createdCount = 0;
        foreach ($localizedPatterns as $patternData) {
            Pattern::create($patternData);
            $createdCount++;
        }

        $this->command->info("Successfully created {$createdCount} localized patterns.");
    }
}
