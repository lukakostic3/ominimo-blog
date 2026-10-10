<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_comment_with_a_name(): void
    {
        $post = Post::factory()->create();

        $this->post(route('comments.store', $post), [
            'guest_name' => 'Pera',
            'comment' => 'Nice post!',
        ])->assertRedirect(route('posts.show', $post));

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => null,
            'guest_name' => 'Pera',
            'comment' => 'Nice post!',
        ]);
    }

    public function test_guest_comment_requires_a_name(): void
    {
        $post = Post::factory()->create();

        $this->post(route('comments.store', $post), ['comment' => 'No name'])
            ->assertSessionHasErrors('guest_name');

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_comment_text_is_required(): void
    {
        $post = Post::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('comments.store', $post), [])
            ->assertSessionHasErrors('comment');
    }

    public function test_authenticated_comment_is_linked_to_user_and_ignores_guest_name(): void
    {
        $post = Post::factory()->create();
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('comments.store', $post), [
            'guest_name' => 'Fake name',
            'comment' => 'Hello',
        ]);

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'guest_name' => null,
        ]);
    }

    public function test_comment_author_can_delete_own_comment(): void
    {
        $comment = Comment::factory()->create();

        $this->actingAs($comment->user)
            ->delete(route('comments.destroy', $comment))
            ->assertRedirect(route('posts.show', $comment->post));

        $this->assertModelMissing($comment);
    }

    public function test_post_owner_can_delete_any_comment_on_their_post(): void
    {
        $comment = Comment::factory()->create();

        $this->actingAs($comment->post->user)
            ->delete(route('comments.destroy', $comment));

        $this->assertModelMissing($comment);
    }

    public function test_other_users_cannot_delete_comment(): void
    {
        $comment = Comment::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('comments.destroy', $comment))
            ->assertForbidden();

        $this->assertModelExists($comment);
    }

    public function test_guests_cannot_delete_comments(): void
    {
        $comment = Comment::factory()->guest()->create();

        $this->delete(route('comments.destroy', $comment))
            ->assertRedirect(route('login'));

        $this->assertModelExists($comment);
    }

    public function test_admin_can_delete_any_comment(): void
    {
        $comment = Comment::factory()->create();

        $this->actingAs(User::factory()->admin()->create())
            ->delete(route('comments.destroy', $comment));

        $this->assertModelMissing($comment);
    }
}
