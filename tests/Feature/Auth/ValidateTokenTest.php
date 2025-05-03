<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ValidateTokenTest extends TestCase
{
    use RefreshDatabase;

    /** @var \App\Models\User */
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Wyłączamy middleware, żeby nie było 403
        $this->withoutMiddleware();

        // Tworzymy użytkownika w bazie
        $this->user = User::factory()->create([
            'email'    => 'tokenuser@example.com',
            'password' => bcrypt('Password1!'),
        ]);
    }

    /** @test */
    public function testSuccessValidateToken()
    {
        $response = $this
            ->actingAs($this->user, 'sanctum')
            ->getJson('/api/token/validate');

        $response->assertStatus(200)
            ->assertJson([
                'valid' => true,
                'user'  => [
                    'id'    => $this->user->id,
                    'email' => $this->user->email,
                ],
            ]);
    }

    /** @test */
    public function testFailsValidateInvalidToken()
    {
        // Skoro middleware wyłączone, trafiamy bez usera do kontrolera
        $response = $this
            ->getJson('/api/token/validate');

        $response->assertStatus(401)
            ->assertJson([
                'valid'   => false,
                'message' => 'Invalid token',
            ]);
    }
}
