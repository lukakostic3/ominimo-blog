<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiCommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_comment_via_api(): void
    {
        $post = Post::factory()->create();

        $this->postJson("/api/posts/{$post->id}/comments", [
            'guest_name' => 'Pera',
            'comment' => 'Hello from API',
        ])
            ->assertCreated()
            ->assertJsonPath('data.author_name', 'Pera')
            ->assertJsonPath('data.is_guest', true);
    }

    public function test_guest_comment_requires_name(): void
    {
        $post = Post::factory()->create();

        $this->postJson("/api/posts/{$post->id}/comments", ['comment' => 'x'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('guest_name');
    }

    public function test_post_owner_can_delete_comment(): void
    {
        $comment = Comment::factory()->create();
        Sanctum::actingAs($comment->post->user);

        $this->deleteJson("/api/comments/{$comment->id}")->assertNoContent();

        $this->assertModelMissing($comment);
    }

    public function test_other_user_cannot_delete_comment(): void
    {
        $comment = Comment::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->deleteJson("/api/comments/{$comment->id}")->assertForbidden();
    }

    public function test_guest_cannot_delete_comment(): void
    {
        $comment = Comment::factory()->create();

        $this->deleteJson("/api/comments/{$comment->id}")->assertUnauthorized();
    }
}
