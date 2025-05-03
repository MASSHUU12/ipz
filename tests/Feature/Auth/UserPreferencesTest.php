<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Database\Seeders\RolesAndPermissionsSeeder;

class UserPreferencesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Zasadź role i uprawnienia
        $this->seed(RolesAndPermissionsSeeder::class);

        // Utwórz użytkownika z rolą User, bez weryfikacji
        $this->user = User::factory()->create([
            'email'                    => 'prefuser@example.com',
            'password'                 => bcrypt('Password1!'),
            'email_verified_at'        => null,
            'phone_number'             => '+48123456789',
            'phone_number_verified_at' => null,
        ]);
        $this->user->assignRole('User');
    }

    /** @test */
    public function testSuccessGetCurrentUserPreferences()
    {
        // Nadaj weryfikację e-mail
        $this->user->email_verified_at = now();
        $this->user->save();

        // Załóż lub nadpisz preferencje (updateOrInsert eliminuje duplikaty)
        DB::table('user_preferences')->updateOrInsert(
            ['user_id' => $this->user->id],
            [
                'notice_method'           => 'E-mail',
                'city'                    => 'Szczecin',
                'meteorological_warnings' => true,
                'hydrological_warnings'   => false,
                'air_quality_warnings'    => false,
                'temperature_warning'     => true,
                'temperature_check_value' => 25.0,
                'created_at'              => now(),
                'updated_at'              => now(),
            ]
        );

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/user/preferences');

        $response->assertStatus(200)
            ->assertJson([
                'preferences' => [
                    'user_id'                 => $this->user->id,
                    'notice_method'           => 'E-mail',
                    'city'                    => 'Szczecin',
                    'meteorological_warnings' => true,
                    'hydrological_warnings'   => false,
                    'air_quality_warnings'    => false,
                    'temperature_warning'     => true,
                    'temperature_check_value' => 25.0,
                ],
            ]);
    }

    /** @test */
    public function testFailsGetCurrentUserPreferencesUnverified()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/user/preferences');

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'You need to verify either email or phone number.',
            ]);
    }

    /** @test */
    public function testSuccessUpdateCurrentUserPreferences()
    {
        // Nadaj weryfikację e-mail
        $this->user->email_verified_at = now();
        $this->user->save();

        // Upewnij się, że jest jakiś wpis (może zostać nadpisany przez kontroler)
        DB::table('user_preferences')->updateOrInsert(
            ['user_id' => $this->user->id],
            ['created_at' => now(), 'updated_at' => now()]
        );

        $payload = [
            'notice_method'           => 'Both',
            'city'                    => 'Gdańsk',
            'meteorological_warnings' => false,
            'hydrological_warnings'   => true,
            'air_quality_warnings'    => true,
            'temperature_warning'     => false,
            'temperature_check_value' => 10.0,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson('/api/user/preferences', $payload);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'User preferences updated successfully.'])
            ->assertJsonPath('preferences.city', 'Gdańsk')
            ->assertJsonPath('preferences.notice_method', 'Both');

        $this->assertDatabaseHas('user_preferences', array_merge(
            ['user_id' => $this->user->id],
            $payload
        ));
    }

    /** @test */
    public function testFailsUpdateCurrentUserPreferencesUnverified()
    {
        $payload = [
            'notice_method'           => 'SMS',
            'city'                    => 'Warszawa',
            'meteorological_warnings' => true,
            'hydrological_warnings'   => false,
            'air_quality_warnings'    => false,
            'temperature_warning'     => true,
            'temperature_check_value' => 20.0,
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson('/api/user/preferences', $payload);

        $response->assertStatus(403)
            ->assertJson([
                'message' => 'You need to verify either email or phone number.',
            ]);
    }
}
