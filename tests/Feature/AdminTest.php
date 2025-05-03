<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Inertia\Testing\AssertableInertia as Assert;
use Database\Seeders\RolesAndPermissionsSeeder;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Zasadź role (w razie potrzeby dla innych testów)
        $this->seed(RolesAndPermissionsSeeder::class);

        // Zapobiegamy próbom odczytu mix-manifest.json
        Config::set('inertia.version', function ($request) {
            return 'test';
        });
    }

    /** @test */
    public function testRenderAdminPage()
    {
        // Wysyłamy GET z Accept: application/json
        $response = $this->getJson('/idkfa');

        $response->assertStatus(200)
            ->assertInertia(
                fn(Assert $page) =>
                $page->component('admin')
            );
    }

    /** @test */
    public function testAccessAdmintestAsSuperAdmin()
    {
        // Utwórz użytkownika i nadaj mu rolę Super Admin
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/admintest');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Sphinx of black quartz, judge my vow',
            ]);
    }

    /** @test */
    public function testForbiddenAccessAdmintestAsRegularUser()
    {
        // Utwórz użytkownika z rolą User
        $user = User::factory()->create();
        $user->assignRole('User');

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/admintest');

        $response->assertStatus(403);
    }

    /** @test */
    public function testUnauthorizedAccessAdmintestWithoutAuthentication()
    {
        $response = $this->getJson('/api/admintest');

        $response->assertStatus(401);
    }
}
