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

    public function test_veryfied_user_can_get_default_preferences()
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
    }


    public function test_verified_user_can_update_preferences()
    {
        $user = User::factory()->create();
        $user->assignRole('User');

        $playload = [
            'notice_method' => 'E-mail',
            'city' => 'Warszawa',
            'meteorological_warnings' => false,
            'hydrological_warnings' => true,
            'air_quality_warnings' => true,
            'temperature_warning' => true,
            'temperature_check_value' => 23.5,
        ];

        $this->actingAs($user, 'sanctum')
            ->json('PATCH', '/api/user/preferences', $playload)
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
                'city' => 'Warszawa',
            ]);
        $this->assertDatabaseHas(
            'user_preferences',
            array_merge(['user_id' => $user->id], $playload)
        );
    }

    public function test_partial_update_only_city_does_not_change_other_preferences()
    {
        $user = User::factory()->create();
        $user->assignRole('User');

        $this->actingAs($user, 'sanctum')
            ->json('PATCH', '/api/user/preferences', [
                'notice_method' => 'Both',
                'city' => 'Gdańsk',
                'meteorological_warnings' => true,
                'hydrological_warnings' => true,
                'air_quality_warnings' => false,
                'temperature_warning' => false,
                'temperature_check_value' => 5,
            ]);

        $this->actingAs($user, 'sanctum')
            ->json('PATCH', '/api/user/preferences', [
                'city' => 'Kraków',
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['city' => 'Kraków']);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'notice_method' => 'Both',
            'city' => 'Kraków',
            'meteorological_warnings' => true,
            'hydrological_warnings' => true,
            'air_quality_warnings' => false,
            'temperature_warning' => false,
            'temperature_check_value' => '5.00',
        ]);
    }

    public function test_reset_city_to_null()
    {
        $user = User::factory()->create();
        $user->assignRole('User');
        $this->actingAs($user, 'sanctum')
            ->json('PATCH', '/api/user/preferences', [
                'city' => null,
            ])
            ->assertStatus(200)
            ->assertJsonFragment(['city' => null]);

        $this->assertDatabaseHas('user_preferences', [
            'user_id' => $user->id,
            'city' => null,
        ]);
    }

    public function test_validation_error_for_invalid_notice_method()
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

    public function test_validation_error_for_city_too_long()
    {
        $user = User::factory()->create();
        $user->assignRole('User');

        $this->actingAs($user, 'sanctum')
            ->json('PATCH', '/api/user/preferences', [
                'city' => str_repeat('a', 256),
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['city']);
    }

    public function test_validation_error_for_non_muneric_temperature_check_value()
    {
        $user = User::factory()->create();
        $user->assignRole('User');

        $this->actingAs($user, 'sanctum')
            ->json('PATCH', '/api/user/preferences', [
                'temperature_check_value' => 'abc',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['temperature_check_value']);
    }

    public function test_validation_error_for_invalid_meteorological_warnings()
    {
        $user = User::factory()->create();
        $user->assignRole('User');

        $this->actingAs($user, 'sanctum')
            ->json('PATCH', '/api/user/preferences', [
                'meteorological_warnings' => 'abc',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['meteorological_warnings']);
    }

    public function test_validation_error_for_invalid_hydrological_warnings()
    {
        $user = User::factory()->create();
        $user->assignRole('User');

        $this->actingAs($user, 'sanctum')
            ->json('PATCH', '/api/user/preferences', [
                'hydrological_warnings' => 'abc',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['hydrological_warnings']);
    }

    public function test_validation_error_for_invalid_air_quality_warnings()
    {
        $user = User::factory()->create();
        $user->assignRole('User');

        $this->actingAs($user, 'sanctum')
            ->json('PATCH', '/api/user/preferences', [
                'air_quality_warnings' => 'abc',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['air_quality_warnings']);
    }

    public function test_validation_error_for_invalid_temperature_warning()
    {
        $user = User::factory()->create();
        $user->assignRole('User');

        $this->actingAs($user, 'sanctum')
            ->json('PATCH', '/api/user/preferences', [
                'temperature_warning' => 'abc',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['temperature_warning']);
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
