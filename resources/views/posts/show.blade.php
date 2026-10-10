<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $post->title }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <x-flash />
            <div data-vue="post-show"
                 data-props="{{ json_encode(['postId' => $post->id, 'isGuest' => auth()->guest()]) }}"></div>
        </div>
    </div>
</x-app-layout>