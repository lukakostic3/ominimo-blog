<?php

namespace Tests\Unit;

use App\Models\Comment;
use App\Models\User;
use Tests\TestCase;

class CommentAuthorNameTest extends TestCase
{
    public function test_returns_user_name_for_registered_users(): void
    {
        $comment = new Comment();
        $comment->setRelation('user', new User(['name' => 'Luka']));

        $this->assertSame('Luka', $comment->authorName());
    }

    public function test_returns_guest_name_for_guests(): void
    {
        $comment = new Comment(['guest_name' => 'Pera']);
        $comment->setRelation('user', null);

        $this->assertSame('Pera', $comment->authorName());
    }

    public function test_falls_back_to_guest_label(): void
    {
        $comment = new Comment();
        $comment->setRelation('user', null);

        $this->assertSame('Guest', $comment->authorName());
    }
}