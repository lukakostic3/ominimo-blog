<script setup>
import { ref, onMounted } from 'vue';
import api from '../api';
import { formatDate } from '../utils';

const posts = ref([]);
const meta = ref(null);
const loading = ref(true);
const error = ref(null);

async function load(page = 1) {
    loading.value = true;
    error.value = null;

    try {
        const { data } = await api.get('/posts', { params: { page } });
        posts.value = data.data;
        meta.value = data.meta;
        window.scrollTo({ top: 0 });
    } catch {
        error.value = 'Could not load posts.';
    } finally {
        loading.value = false;
    }
}

onMounted(() => load());
</script>

<template>
    <div class="space-y-4">
        <p v-if="loading" class="text-gray-500">Loading posts…</p>
        <p v-else-if="error" class="text-red-600">{{ error }}</p>

        <template v-else>
            <p v-if="posts.length === 0" class="text-gray-500">No posts yet.</p>

            <article v-for="post in posts" :key="post.id" class="bg-white p-6 shadow-sm sm:rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900">
                    <a :href="`/posts/${post.id}`" class="hover:underline">{{ post.title }}</a>
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    by {{ post.author.name }} · {{ formatDate(post.created_at) }} · {{ post.comments_count }} comments
                </p>
                <p class="mt-3 text-gray-700">{{ post.excerpt }}</p>
            </article>

            <div v-if="meta && meta.last_page > 1" class="flex items-center justify-between pt-2">
                <button
                    type="button"
                    :disabled="meta.current_page === 1"
                    class="text-sm text-gray-700 hover:underline disabled:opacity-40 disabled:no-underline"
                    @click="load(meta.current_page - 1)"
                >
                    ← Previous
                </button>
                <span class="text-sm text-gray-500">Page {{ meta.current_page }} of {{ meta.last_page }}</span>
                <button
                    type="button"
                    :disabled="meta.current_page === meta.last_page"
                    class="text-sm text-gray-700 hover:underline disabled:opacity-40 disabled:no-underline"
                    @click="load(meta.current_page + 1)"
                >
                    Next →
                </button>
            </div>
        </template>
    </div>
</template>