<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class UserPreferencesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure the 'User' role exists for role middleware
        Role::firstOrCreate(['name' => 'User']);
    }

    public function test_unauthenticated_users_receive_401()
    {
        $this->json('GET', '/api/user/preferences')
            ->assertStatus(401);

        $this->json('PATCH', '/api/user/preferences', [
            'notice_method' => 'E-mail',
        ])
            ->assertStatus(401);
    }

    public function test_unverified_users_receive_403()
    {
        $user = User::factory()->unverified()->create();
        $user->assignRole('User');

        $this->actingAs($user, 'sanctum')
            ->json('GET', '/api/user/preferences')
            ->assertStatus(403);

        $this->actingAs($user, 'sanctum')
            ->json('PATCH', '/api/user/preferences', [
                'notice_method' => 'E-mail',
            ])
            ->assertStatus(403);
    }

    public function test_verified_user_can_get_and_update_preferences()
    {
        $user = User::factory()->create();
        $user->assignRole('User');

        // GET default preferences
        $this->actingAs($user, 'sanctum')
            ->json('GET', '/api/user/preferences')
            ->assertStatus(200)
            ->assertJsonStructure([
                'preferences' => [
                    'notice_method',
                    'city',
                    'meteorological_warnings',
                    'hydrological_warnings',
                    'air_quality_warnings',
                    'temperature_warning',
                    'temperature_check_value',
                ],
            ])
            ->assertJsonFragment([
                'notice_method' => 'E-mail',
                'city' => null,
                'meteorological_warnings' => false,
                'hydrological_warnings' => false,
                'air_quality_warnings' => false,
                'temperature_warning' => false,
                'temperature_check_value' => '10.00',
            ]);

        $payload = [
            'notice_method' => 'E-mail',
            'city' => 'Szczecin',
            'meteorological_warnings' => false,
            'hydrological_warnings' => true,
            'air_quality_warnings' => true,
            'temperature_warning' => true,
            'temperature_check_value' => 23.5,
        ];

        // PATCH update preferences
        $this->actingAs($user, 'sanctum')
            ->json('PATCH', '/api/user/preferences', $payload)
            ->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'preferences' => [
                    'notice_method',
                    'city',
                    'meteorological_warnings',
                    'hydrological_warnings',
                    'air_quality_warnings',
                    'temperature_warning',
                    'temperature_check_value',
                ],
            ])
            ->assertJsonFragment([
                'notice_method' => 'E-mail',
                'city' => 'Szczecin',
            ]);

        // Database state
        $this->assertDatabaseHas(
            'user_preferences',
            array_merge(['user_id' => $user->id], $payload)
        );
    }

    public function test_validation_errors_for_invalid_data()
    {
        $user = User::factory()->create();
        $user->assignRole('User');

        $this->actingAs($user, 'sanctum')
            ->json('PATCH', '/api/user/preferences', [
                'notice_method' => 'invalid_method',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['notice_method']);
    }
}
