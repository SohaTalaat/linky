<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{

    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        $res = $this->postJson('/api/register', [
            'name' => 'Soha',
            'email' => 'soha@test.com',
            'password' => 'password123',
        ])->assertCreated();

        $res->assertJsonStructure([
            'user' => ['id', 'name', 'email'],
            'token',
        ]);
    }

    public function test_user_can_login_and_get_token(): void
    {
        User::factory()->create([
            'email' => 'soha@test.com',
            'password' => bcrypt('password123'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'soha@test.com',
            'password' => 'password123',
        ])->assertOk()->assertJsonStructure(['token']);
    }

    public function test_me_requires_auth(): void
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    public function test_authenticated_user_can_get_me(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/me')
            ->assertOk()
            ->assertJsonPath('user.id', $user->id);
    }
}
