<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Models\User */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Wyłączamy wszystkie middleware, żeby nie były przyczyną 403
        $this->withoutMiddleware();

        // Tworzymy użytkownika w bazie
        $this->user = User::factory()->create([
            'email'        => 'user@example.com',
            'phone_number' => '+48111111111',
            'password'     => bcrypt('Password1!'),
        ]);
    }

    /** @test */
    public function testSuccessGetCurrentUser(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/user');

        $response->assertStatus(200)
            ->assertJson([
                'user' => [
                    'id'           => $this->user->id,
                    'email'        => $this->user->email,
                    'phone_number' => $this->user->phone_number,
                ],
            ]);
    }

    /** @test */
    public function testSuccessUpdateOnlyEmail(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson('/api/user', [
                'email' => 'new@example.com',
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'User updated successfully'])
            ->assertJsonPath('user.email', 'new@example.com');

        $this->assertDatabaseHas('users', [
            'id'    => $this->user->id,
            'email' => 'new@example.com',
        ]);
    }

    /** @test */
    public function testSuccessUpdatePassword(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson('/api/user/password', [
                'current_password' => 'Password1!',
                'new_password' => 'Zaq1@wsx',
                'new_password_confirmation' => 'Zaq1@wsx'
            ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Password updated successfully.']);

        // Sprawdzamy, że hasło zostało faktycznie zaktualizowane
        $fresh = User::find($this->user->id);
        $this->assertTrue(password_verify('Zaq1@wsx', $fresh->password));
    }

    /** @test */
    public function testFailsUpdateInvalidEmailFormat(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson('/api/user', [
                'email' => 'invalid-email',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function testFailsUpdateDuplicateEmail(): void
    {
        // Tworzymy drugi użytkownik z tym samym emailem
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson('/api/user', [
                'email' => 'existing@example.com',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function testFailsUpdateTooLongPhoneNumber(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson('/api/user', [
                'phone_number' => str_repeat('1', 21),
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);
    }

    /** @test */
    public function testFailsUpdateWeakPassword(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->patchJson('/api/user/password', [
                'current_password' => 'Password1!',
                'new_password' => 'yo',
                'new_password_confirmation' => 'yo'
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    /** @test */
    public function testSuccessDeleteCurrentUser(): void
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson('/api/user');

        $response->assertStatus(200)
            ->assertJson(['message' => 'User deleted successfully']);

        $this->assertDatabaseMissing('users', ['id' => $this->user->id]);
    }
}
