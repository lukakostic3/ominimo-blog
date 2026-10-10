<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Support\Facades\Gate;

class CommentController extends Controller
{
    public function store(StoreCommentRequest $request, Post $post)
    {
        $comment = $post->comments()->make([
            'comment' => $request->validated('comment'),
        ]);

        if ($request->user()) {
            $comment->user()->associate($request->user());
        } else {
            $comment->guest_name = $request->validated('guest_name');
        }

        $comment->save();

        return redirect()->route('posts.show', $post)
            ->with('status', 'Comment added.');
    }

    public function destroy(Comment $comment)
    {
        Gate::authorize('delete', $comment);

        $post = $comment->post;
        $comment->delete();

        return redirect()->route('posts.show', $post)
            ->with('status', 'Comment deleted.');
    }
}