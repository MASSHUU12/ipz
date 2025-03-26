<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Http\Middleware\CheckUserBlocked;
use Illuminate\Support\Carbon;
use Tests\TestCase;
use function PHPUnit\Framework\assertNotNull;

class TestsLogin extends TestCase
{
    use RefreshDatabase;

    // poprawne logowanie przy użyciu e-mail
    public function testSuccessLoginWithEmail()
    {
        $password = 'Password1!';
        $user = User::factory()->create([
            'email' => 'testowy@example.com',
            'password' => password_hash($password, PASSWORD_DEFAULT),
        ]);

        $data = [
            'email' => $user->email,
            'password' => $password,
        ];

        $response = $this->postJson('/login', $data);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'user' => ['id', 'email'],
                'token'
            ]);
    }

    // logowanie z błędnymi danymi
    public function testLoginFailsWithIncorrectCredentials()
    {
        User::factory()->create(['email' => 'testowy@example.com',
            'password' => password_hash('Password1!', PASSWORD_DEFAULT)]);

        $data = [
            'email' => 'testowy@example.com',
            'password' => "WrongPassword!",
        ];

        $response = $this->postJson('/login', $data);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Podane dane uwierzytelniające są nieprawidłowe.']);
    }

    // zablokowanie konta po 5 nieudanych próbach logowania
    public function testAccountGetBlockedAfterFiveFailedAttempts()
    {
        $user = User::factory()->create([
            'email' => 'testowy@example.com',
            'password' => password_hash('Password1!', PASSWORD_DEFAULT)]);

        for ($i = 0; $i < 5; $i++){
            $this->postJson('/login',[
                'email' => 'testowy@example.com',
                'password' => 'Password1!',
            ]);
        }

        $response = $this->postJson('/login',[
            'email' => 'testowy@example.com',
            'password' => 'Password1!',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Twoje konto jest chwilowo zablokowane. Spróbuj ponownie później.']);

        $user->refresh();
        $this->assertNotNull($user->blocked_until);
    }
}