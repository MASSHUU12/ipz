<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Inertia\Testing\AssertableInertia as Assert;
use Database\Seeders\RolesAndPermissionsSeeder;
use App\Http\Middleware\HandleInertiaRequests;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Zasadź role (potrzebne w pozostałych testach)
        $this->seed(RolesAndPermissionsSeeder::class);

        // Wyłączamy tylko Inertia-middleware, żeby nie próbowało czytać manifestu
        $this->withoutMiddleware(HandleInertiaRequests::class);
    }

    /** @test */
    public function testRenderAdminPage()
    {
        // Teraz prostym GET bez dodatkowych nagłówków
        $response = $this->get('/idkfa');

        $response->assertStatus(200)
            ->assertInertia(
                fn(Assert $page) =>
                $page->component('admin')
            );
    }

    /** @test */
    public function testAccessAdmintestAsSuperAdmin()
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/admintest')
            ->assertStatus(200)
            ->assertJson(['message' => 'Sphinx of black quartz, judge my vow']);
    }

    /** @test */
    public function testForbiddenAccessAdmintestAsRegularUser()
    {
        $user = User::factory()->create();
        $user->assignRole('User');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/admintest')
            ->assertStatus(403);
    }

    /** @test */
    public function testUnauthorizedAccessAdmintestWithoutAuthentication()
    {
        $this->getJson('/api/admintest')
            ->assertStatus(401);
    }
}
