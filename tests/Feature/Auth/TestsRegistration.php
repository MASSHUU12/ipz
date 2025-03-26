<?php

namespace Tests\Feature\Auth;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use App\Http\Controllers\AuthController;
use Tests\TestCase;
class TestsRegistration extends TestCase
{
    use RefreshDatabase;

    // poprawna rejestracja przy użyciu e-mail
    public function testSuccessfulRegistrationEmail()
    {
        $data = [
            'email' => 'test@example.com',
            'password' => 'Password1!',
            'password_confirmation' => 'Password1!',
        ];
        $response = $this->postJson('/register', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'email'],
                'token'
            ]);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    // poprawna rejestracja przy użyciu numeru telefonu
    public function testSuccessfulRegistrationPhoneNumber()
    {
        $data = [
            'phone_number'          => '+48123456789',
            'password'              => 'Password1!',
            'password_confirmation' => 'Password1!',
        ];

        $response = $this->postJson('/register', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'phone_number'],
                'token'
            ]);
        $this->assertDatabaseHas('users', ['phone_number' => '+48123456789']);
    }

    // rejestracja bez podania e-mail i numeru telefonu
    public function testRegistrationWithoutEmailAndPhone()
    {
        $data = [
            'password'              => 'Password1!',
            'password_confirmation' => 'Password1!',
        ];
        $response = $this->postJson('/register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'phone_number']);
    }

    // rejestracja z niepoprawnym formatem telefonu
    public function testRegistrationWithInvalidPhone()
    {
        $data = [
            'phone_number'          => '456789',
            'password'              => 'Password1!',
            'password_confirmation' => 'Password1!',
        ];
        $response = $this->postJson('/register', $data);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['phone_number']);
    }
}