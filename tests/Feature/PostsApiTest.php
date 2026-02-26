<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class PostsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_posts(): void
    {
        Post::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/posts?per_page=2&sort=-created_at');

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure([
                'data' => [
                    ['id', 'title', 'content', 'user_id', 'created_at', 'updated_at'],
                ],
                'links',
                'meta',
            ])
            ->assertJsonPath('meta.per_page', 2);
    }

    public function test_guest_cannot_create_post(): void
    {
        $response = $this->postJson('/api/v1/posts', [
            'title' => 'First API Post',
            'content' => 'Post body',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'title' => 'First API Post',
            'content' => 'Post body',
        ];

        $response = $this->postJson('/api/v1/posts', $payload);

        $response
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJsonPath('data.title', $payload['title'])
            ->assertJsonPath('data.content', $payload['content'])
            ->assertJsonPath('data.user_id', $user->id);

        $this->assertDatabaseHas('posts', [
            ...$payload,
            'user_id' => $user->id,
        ]);
    }

    public function test_show_returns_single_post(): void
    {
        $post = Post::factory()->create();

        $response = $this->getJson("/api/v1/posts/{$post->id}");

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.id', $post->id)
            ->assertJsonPath('data.title', $post->title);
    }

    public function test_update_modifies_post(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $post = Post::factory()->for($user)->create();

        $response = $this->patchJson("/api/v1/posts/{$post->id}", [
            'title' => 'Updated title',
        ]);

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonPath('data.title', 'Updated title');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated title',
        ]);
    }

    public function test_destroy_deletes_post(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $post = Post::factory()->for($user)->create();

        $response = $this->deleteJson("/api/v1/posts/{$post->id}");

        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertDatabaseMissing('posts', ['id' => $post->id]);
    }

    public function test_non_owner_cannot_update_or_delete_post(): void
    {
        $owner = User::factory()->create();
        $attacker = User::factory()->create();
        $post = Post::factory()->for($owner)->create();

        Sanctum::actingAs($attacker);

        $this->patchJson("/api/v1/posts/{$post->id}", [
            'title' => 'Hacked',
        ])->assertStatus(Response::HTTP_FORBIDDEN);

        $this->deleteJson("/api/v1/posts/{$post->id}")
            ->assertStatus(Response::HTTP_FORBIDDEN);
    }

    public function test_store_validation_errors_have_consistent_format(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $response = $this->postJson('/api/v1/posts', []);

        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonStructure([
                'message',
                'errors' => [
                    ['field', 'detail'],
                ],
            ]);
    }

    public function test_unknown_route_returns_json_404(): void
    {
        $response = $this->getJson('/api/v1/unknown-endpoint');

        $response
            ->assertStatus(Response::HTTP_NOT_FOUND)
            ->assertJsonStructure(['message', 'errors']);
    }
}
