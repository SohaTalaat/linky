<?php

namespace Tests\Feature;

use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LinkApiTest extends TestCase
{

    use RefreshDatabase;

    public function test_guest_cannot_access_links(): void
    {
        $this->postJson('/api/links', ['url' => 'https://example.com'])
            ->assertStatus(401);
    }

    public function test_user_can_create_link_with_tags(): void
    {
        $user = User::factory()->create();

        $payload = [
            'url' => 'https://laravel.com',
            'title' => 'Laravel',
            'notes' => 'Docs',
            'status' => 'saved',
            'is_favorite' => true,
            'tags' => ['laravel', 'backend'],
        ];

        $res = $this->actingAs($user, 'sanctum')
            ->postJson('/api/links', $payload)
            ->assertStatus(201)
            ->assertJsonPath('data.url', 'https://laravel.com');

        $this->assertDatabaseHas('links', [
            'user_id' => $user->id,
            'url' => 'https://laravel.com',
            'is_favorite' => 1,
        ]);

        $linkId = $res->json('data.id');

        // pivot exists
        $this->assertDatabaseHas('link_tag', [
            'link_id' => $linkId,
        ]);
    }

    public function test_user_can_list_with_search_and_filters(): void
    {
        $user = User::factory()->create();

        Link::factory()->for($user)->create(['title' => 'Laravel Tips', 'status' => 'saved', 'is_favorite' => 1]);
        Link::factory()->for($user)->create(['title' => 'PHP Basics', 'status' => 'done', 'is_favorite' => 0]);

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/links?search=Laravel')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/links?status=done')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/links?favorite=true')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_user_cannot_access_other_users_link(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $link = Link::factory()->for($userA)->create();

        $this->actingAs($userB, 'sanctum')
            ->getJson("/api/links/{$link->id}")
            ->assertStatus(403);
    }

    public function test_user_can_toggle_favorite(): void
    {
        $user = User::factory()->create();
        $link = Link::factory()->for($user)->create(['is_favorite' => 0]);

        $this->actingAs($user, 'sanctum')
            ->patchJson("/api/links/{$link->id}/favorite")
            ->assertOk()
            ->assertJsonPath('data.is_favorite', true);

        $this->assertDatabaseHas('links', [
            'id' => $link->id,
            'is_favorite' => 1,
        ]);
    }
}
