<?php

namespace Database\Seeders;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        Post::all()->each(function (Post $post) use ($users) {
            $createdAt = fn () => ['created_at' => fake()->dateTimeBetween($post->created_at)];

            Comment::factory(fake()->numberBetween(1, 4))
                ->for($post)
                ->recycle($users)
                ->state($createdAt)
                ->create();

            Comment::factory(fake()->numberBetween(0, 2))
                ->guest()
                ->for($post)
                ->state($createdAt)
                ->create();
        });
    }
}
