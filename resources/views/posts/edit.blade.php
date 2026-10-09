<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit post</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('posts.update', $post) }}">
                    @method('PUT')
                    @include('posts._form')

                    <div class="mt-6 flex items-center gap-4">
                        <x-primary-button>Save changes</x-primary-button>
                        <a href="{{ route('posts.show', $post) }}" class="text-sm text-gray-600 hover:underline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>