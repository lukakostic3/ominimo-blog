<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
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
        $comment->setRelation('post', $post);

        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }

    public function destroy(Comment $comment)
    {
        Gate::authorize('delete', $comment);

        $comment->delete();

        return response()->noContent();
    }
}
