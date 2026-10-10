<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use RefreshDatabase;

    private function validData(): array
    {
        return ['title' => 'Test title', 'content' => 'Test content'];
    }

    public function test_guests_can_view_posts_list(): void
    {
        $post = Post::factory()->create();

        $this->get(route('posts.index'))
            ->assertOk()
            ->assertSee($post->title);
    }

    public function test_guests_can_view_a_single_post_with_comments(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->for($post)->create();

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee($post->title)
            ->assertSee($comment->comment);
    }

    public function test_guests_are_redirected_to_login_from_create_form(): void
    {
        $this->get(route('posts.create'))
            ->assertRedirect(route('login'));
    }

    public function test_guests_cannot_store_posts(): void
    {
        $this->post(route('posts.store'), $this->validData())
            ->assertRedirect(route('login'));

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_authenticated_user_can_create_post(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post(route('posts.store'), $this->validData());

        $post = Post::first();
        $response->assertRedirect(route('posts.show', $post));

        $this->assertDatabaseHas('posts', [
            'title' => 'Test title',
            'user_id' => $user->id,
        ]);
    }

    public function test_title_and_content_are_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('posts.store'), [])
            ->assertSessionHasErrors(['title', 'content']);

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_owner_can_update_post(): void
    {
        $post = Post::factory()->create();

        $this->actingAs($post->user)
            ->put(route('posts.update', $post), [
                'title' => 'Updated title',
                'content' => 'Updated content',
            ])
            ->assertRedirect(route('posts.show', $post));

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated title',
        ]);
    }

    public function test_non_owner_cannot_edit_or_update_post(): void
    {
        $post = Post::factory()->create();
        $otherUser = User::factory()->create();

        $this->actingAs($otherUser)
            ->get(route('posts.edit', $post))
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->put(route('posts.update', $post), $this->validData())
            ->assertForbidden();

        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
            'title' => 'Test title',
        ]);
    }

    public function test_owner_can_delete_post(): void
    {
        $post = Post::factory()->create();

        $this->actingAs($post->user)
            ->delete(route('posts.destroy', $post))
            ->assertRedirect(route('posts.index'));

        $this->assertModelMissing($post);
    }

    public function test_non_owner_cannot_delete_post(): void
    {
        $post = Post::factory()->create();

        $this->actingAs(User::factory()->create())
            ->delete(route('posts.destroy', $post))
            ->assertForbidden();

        $this->assertModelExists($post);
    }

    public function test_admin_can_delete_any_post(): void
    {
        $post = Post::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->delete(route('posts.destroy', $post))
            ->assertRedirect(route('posts.index'));

        $this->assertModelMissing($post);
    }

    public function test_admin_cannot_update_other_users_post(): void
    {
        $post = Post::factory()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('posts.update', $post), $this->validData())
            ->assertForbidden();
    }

    public function test_deleting_post_also_deletes_its_comments(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->for($post)->create();

        $this->actingAs($post->user)->delete(route('posts.destroy', $post));

        $this->assertModelMissing($comment);
    }
}
