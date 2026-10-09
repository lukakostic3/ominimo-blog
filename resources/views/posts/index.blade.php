<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Posts') }}</h2>
            @auth
                <a href="{{ route('posts.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    New post
                </a>
            @endauth
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <x-flash />

            @forelse ($posts as $post)
                <article class="bg-white p-6 shadow-sm sm:rounded-lg">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <a href="{{ route('posts.show', $post) }}" class="hover:underline">{{ $post->title }}</a>
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                        by {{ $post->user->name }} · {{ $post->created_at->diffForHumans() }} · {{ $post->comments_count }} comments
                    </p>
                    <p class="mt-3 text-gray-700">{{ \Illuminate\Support\Str::limit($post->content, 200) }}</p>
                </article>
            @empty
                <p class="text-gray-500">No posts yet.</p>
            @endforelse

            {{ $posts->links() }}
        </div>
    </div>
</x-app-layout>