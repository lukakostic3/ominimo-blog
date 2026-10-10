<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $post->title }}</h2>

            <div class="flex items-center gap-3">
                @can('update', $post)
                    <a href="{{ route('posts.edit', $post) }}" class="text-sm text-gray-600 hover:underline">Edit</a>
                @endcan

                @can('delete', $post)
                    <form method="POST" action="{{ route('posts.destroy', $post) }}"
                          onsubmit="return confirm('Delete this post?');">
                        @csrf
                        @method('DELETE')
                        <x-danger-button>Delete</x-danger-button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <x-flash />

            <article class="bg-white p-6 shadow-sm sm:rounded-lg">
                <p class="text-sm text-gray-500">
                    by {{ $post->user->name }} · {{ $post->created_at->format('d.m.Y H:i') }}
                </p>
                <div class="mt-4 text-gray-800 whitespace-pre-line">{{ $post->content }}</div>
            </article>

            <section class="bg-white p-6 shadow-sm sm:rounded-lg">
            <h3 class="text-lg font-semibold text-gray-900">Comments ({{ $post->comments->count() }})</h3>

            <form method="POST" action="{{ route('comments.store', $post) }}" class="mt-4 space-y-3">
                @csrf

                @guest
                    <div>
                        <x-input-label for="guest_name" value="Your name" />
                        <x-text-input id="guest_name" name="guest_name" type="text" class="mt-1 block w-full"
                                    :value="old('guest_name')" />
                        <x-input-error :messages="$errors->get('guest_name')" class="mt-2" />
                    </div>
                @endguest

                <div>
                    <x-input-label for="comment" value="Comment" />
                    <textarea id="comment" name="comment" rows="3"
                            class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('comment') }}</textarea>
                    <x-input-error :messages="$errors->get('comment')" class="mt-2" />
                </div>

                <x-primary-button>Add comment</x-primary-button>
            </form>

            <div class="mt-6 space-y-4">
                @forelse ($post->comments as $comment)
                    <div class="border-t border-gray-100 pt-4">
                        <div class="flex items-center justify-between">
                            <p class="text-sm text-gray-500">
                                {{ $comment->authorName() }}
                                @if (! $comment->user_id)
                                    <span class="text-xs text-gray-400">(guest)</span>
                                @endif
                                · {{ $comment->created_at->diffForHumans() }}
                            </p>

                            @can('delete', $comment)
                                <form method="POST" action="{{ route('comments.destroy', $comment) }}"
                                    onsubmit="return confirm('Delete this comment?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs text-red-600 hover:underline">Delete</button>
                                </form>
                            @endcan
                        </div>
                        <p class="mt-1 text-gray-800 whitespace-pre-line">{{ $comment->comment }}</p>
                    </div>
                @empty
                    <p class="text-gray-500">No comments yet.</p>
                @endforelse
            </div>
        </section>
        </div>
    </div>
</x-app-layout>