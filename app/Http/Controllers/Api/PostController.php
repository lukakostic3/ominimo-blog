<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Support\Facades\Gate;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('user')
            ->withCount('comments')
            ->latest()
            ->paginate(10);

        return PostResource::collection($posts);
    }

    public function show(Post $post)
    {
        $post->load([
            'user',
            'comments' => fn ($query) => $query->with('user')->latest(),
        ]);

        $post->comments->each->setRelation('post', $post);

        return new PostResource($post);
    }

    public function store(StorePostRequest $request)
    {
        $post = $request->user()->posts()->create($request->validated());
        $post->load('user');

        return (new PostResource($post))
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $post->update($request->validated());
        $post->load('user');

        return new PostResource($post);
    }

    public function destroy(Post $post)
    {
        Gate::authorize('delete', $post);

        $post->delete();

        return response()->noContent();
    }
}
