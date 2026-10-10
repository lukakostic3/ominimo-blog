<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiPostTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_paginated_posts(): void
    {
        Post::factory(12)->create();

        $this->getJson('/api/posts')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonStructure([
                'data' => [[
                    'id', 'title', 'excerpt', 'comments_count', 'created_at',
                    'author' => ['id', 'name'],
                    'can' => ['update', 'delete'],
                ]],
                'meta' => ['current_page', 'last_page'],
            ]);
    }

    public function test_show_returns_post_with_comments(): void
    {
        $post = Post::factory()->create();
        Comment::factory(2)->for($post)->create();
        Comment::factory()->guest()->for($post)->create();

        $this->getJson("/api/posts/{$post->id}")
            ->assertOk()
            ->assertJsonPath('data.title', $post->title)
            ->assertJsonCount(3, 'data.comments');
    }

    public function test_guests_cannot_create_posts(): void
    {
        $this->postJson('/api/posts', ['title' => 'T', 'content' => 'C'])
            ->assertUnauthorized();
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/posts', ['title' => 'API post', 'content' => 'Body'])
            ->assertCreated()
            ->assertJsonPath('data.title', 'API post')
            ->assertJsonPath('data.author.id', $user->id);

        $this->assertDatabaseHas('posts', ['title' => 'API post', 'user_id' => $user->id]);
    }

    public function test_create_post_validates_input(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/posts', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'content']);
    }

    public function test_non_owner_cannot_update_post(): void
    {
        $post = Post::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->putJson("/api/posts/{$post->id}", ['title' => 'Hacked', 'content' => 'x'])
            ->assertForbidden();
    }

    public function test_owner_can_delete_post(): void
    {
        $post = Post::factory()->create();
        Sanctum::actingAs($post->user);

        $this->deleteJson("/api/posts/{$post->id}")->assertNoContent();

        $this->assertModelMissing($post);
    }

    public function test_permission_flags_reflect_current_user(): void
    {
        $post = Post::factory()->create();

        $this->getJson("/api/posts/{$post->id}")
            ->assertJsonPath('data.can.update', false);

        Sanctum::actingAs($post->user);

        $this->getJson("/api/posts/{$post->id}")
            ->assertJsonPath('data.can.update', true)
            ->assertJsonPath('data.can.delete', true);
    }
}