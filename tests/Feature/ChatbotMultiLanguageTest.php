<?php

namespace Tests\Feature;

use App\Models\Pattern;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChatbotMultiLanguageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles and permissions
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    /**
     * Test chatbot responds in English (default)
     */
    public function test_chatbot_responds_in_english_by_default(): void
    {
        // Create a simple English pattern
        Pattern::create([
            'pattern' => '/\\b(?:hello|hi)\\b/i',
            'responses' => ['Hello! How can I help you?'],
            'severity' => 'low',
            'priority' => 10,
            'enabled' => true,
            'access_level' => 'public',
        ]);

        $response = $this->postJson('/api/chatbot/message', [
            'content' => 'hello',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['question', 'answer', 'payload'])
            ->assertJsonFragment(['question' => 'hello']);
        
        $this->assertStringContainsString('Hello', $response->json('answer'));
    }

    /**
     * Test chatbot responds in Polish when locale is specified
     */
    public function test_chatbot_responds_in_polish_when_locale_specified(): void
    {
        // Create a localized pattern
        Pattern::create([
            'pattern' => [
                'en' => '/\\b(?:hello|hi)\\b/i',
                'pl' => '/\\b(?:cześć|czesc|hej)\\b/iu',
            ],
            'responses' => [
                'en' => ['Hello! How can I help you?'],
                'pl' => ['Cześć! Jak mogę Ci pomóc?'],
            ],
            'severity' => 'low',
            'priority' => 10,
            'enabled' => true,
            'access_level' => 'public',
        ]);

        $response = $this->postJson('/api/chatbot/message', [
            'content' => 'czesc',
            'locale' => 'pl',
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('Cześć', $response->json('answer'));
    }

    /**
     * Test chatbot uses user preference locale
     */
    public function test_chatbot_uses_user_preference_locale(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $user->assignRole('User');

        // Create or update user preferences with Polish locale
        DB::table('user_preferences')->updateOrInsert(
            ['user_id' => $user->id],
            [
                'locale' => 'pl',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Create a localized pattern
        Pattern::create([
            'pattern' => [
                'en' => '/\\b(?:help)\\b/i',
                'pl' => '/\\b(?:pomoc|pomocy)\\b/i',
            ],
            'responses' => [
                'en' => ['I am here to help!'],
                'pl' => ['Jestem tu, aby pomóc!'],
            ],
            'severity' => 'high',
            'priority' => 50,
            'enabled' => true,
            'access_level' => 'public',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/chatbot/message', [
                'content' => 'pomoc',
            ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('pomóc', $response->json('answer'));
    }

    /**
     * Test fallback to English when translation missing
     */
    public function test_chatbot_fallback_to_english_when_translation_missing(): void
    {
        // Create pattern with only English translation
        Pattern::create([
            'pattern' => [
                'en' => '/\\b(?:test)\\b/i',
            ],
            'responses' => [
                'en' => ['This is a test response.'],
            ],
            'severity' => 'low',
            'priority' => 10,
            'enabled' => true,
            'access_level' => 'public',
        ]);

        // Request with Polish locale but pattern has only English
        $response = $this->postJson('/api/chatbot/message', [
            'content' => 'test',
            'locale' => 'pl',
        ]);

        $response->assertStatus(200);
        // Should fallback to English response
        $this->assertStringContainsString('test response', $response->json('answer'));
    }

    /**
     * Test legacy string patterns still work
     */
    public function test_chatbot_legacy_string_patterns_work(): void
    {
        // Create legacy pattern (old format with string instead of array)
        Pattern::create([
            'pattern' => '/\\b(?:legacy)\\b/i',
            'responses' => ['This is a legacy pattern response.'],
            'severity' => 'low',
            'priority' => 10,
            'enabled' => true,
            'access_level' => 'public',
        ]);

        $response = $this->postJson('/api/chatbot/message', [
            'content' => 'legacy',
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('legacy pattern', $response->json('answer'));
    }

    /**
     * Test language change via request parameter
     */
    public function test_chatbot_accepts_language_parameter(): void
    {
        Pattern::create([
            'pattern' => [
                'en' => '/\\b(?:test)\\b/i',
                'pl' => '/\\b(?:test)\\b/i',
            ],
            'responses' => [
                'en' => ['English response'],
                'pl' => ['Polska odpowiedź'],
            ],
            'severity' => 'low',
            'priority' => 10,
            'enabled' => true,
            'access_level' => 'public',
        ]);

        // Test with 'language' parameter
        $response = $this->postJson('/api/chatbot/message', [
            'content' => 'test',
            'language' => 'pl',
        ]);

        $response->assertStatus(200);
        $this->assertStringContainsString('Polska', $response->json('answer'));
    }
}
