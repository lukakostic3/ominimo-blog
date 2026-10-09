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

                {{-- Forma za komentar dolazi u fazi 4 --}}

                <div class="mt-4 space-y-4">
                    @forelse ($post->comments as $comment)
                        <div class="border-t border-gray-100 pt-4">
                            <p class="text-sm text-gray-500">
                                {{ $comment->authorName() }} · {{ $comment->created_at->diffForHumans() }}
                            </p>
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