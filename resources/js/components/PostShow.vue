<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../api';
import { formatDate } from '../utils';

const props = defineProps({
    postId: { type: Number, required: true },
    isGuest: { type: Boolean, default: true },
});

const post = ref(null);
const loading = ref(true);
const loadError = ref(null);

const form = reactive({ guest_name: '', comment: '' });
const errors = ref({});
const submitting = ref(false);
const formMessage = ref(null);

async function loadPost() {
    try {
        const { data } = await api.get(`/posts/${props.postId}`);
        post.value = data.data;
    } catch (e) {
        loadError.value = e.response?.status === 404 ? 'Post not found.' : 'Could not load the post.';
    } finally {
        loading.value = false;
    }
}

async function deletePost() {
    if (!confirm('Delete this post?')) return;

    try {
        await api.delete(`/posts/${props.postId}`);
        window.location.href = '/posts';
    } catch {
        alert('Could not delete the post.');
    }
}

async function addComment() {
    submitting.value = true;
    errors.value = {};
    formMessage.value = null;

    try {
        const payload = props.isGuest ? { ...form } : { comment: form.comment };
        const { data } = await api.post(`/posts/${props.postId}/comments`, payload);
        post.value.comments.unshift(data.data);
        form.comment = '';
    } catch (e) {
        if (e.response?.status === 422) {
            errors.value = e.response.data.errors;
        } else if (e.response?.status === 429) {
            formMessage.value = 'Too many comments. Please wait a minute.';
        } else {
            formMessage.value = 'Something went wrong. Please try again.';
        }
    } finally {
        submitting.value = false;
    }
}

async function deleteComment(comment) {
    if (!confirm('Delete this comment?')) return;

    try {
        await api.delete(`/comments/${comment.id}`);
        post.value.comments = post.value.comments.filter((c) => c.id !== comment.id);
    } catch {
        alert('Could not delete the comment.');
    }
}

onMounted(loadPost);
</script>

<template>
    <p v-if="loading" class="text-gray-500">Loading…</p>
    <p v-else-if="loadError" class="text-red-600">{{ loadError }}</p>

    <div v-else class="space-y-6">
        <article class="bg-white p-6 shadow-sm sm:rounded-lg">
            <div class="flex items-start justify-between gap-4">
                <p class="text-sm text-gray-500">
                    by {{ post.author.name }} · {{ formatDate(post.created_at) }}
                </p>
                <div class="flex items-center gap-4">
                    <a v-if="post.can.update" :href="`/posts/${post.id}/edit`" class="text-sm text-gray-600 hover:underline">Edit</a>
                    <button v-if="post.can.delete" type="button" class="text-sm text-red-600 hover:underline" @click="deletePost">
                        Delete
                    </button>
                </div>
            </div>
            <div class="mt-4 text-gray-800 whitespace-pre-line">{{ post.content }}</div>
        </article>

        <section class="bg-white p-6 shadow-sm sm:rounded-lg">
            <h3 class="text-lg font-semibold text-gray-900">Comments ({{ post.comments.length }})</h3>

            <form class="mt-4 space-y-3" @submit.prevent="addComment">
                <div v-if="isGuest">
                    <label for="guest_name" class="block font-medium text-sm text-gray-700">Your name</label>
                    <input
                        id="guest_name"
                        v-model="form.guest_name"
                        type="text"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    >
                    <p v-if="errors.guest_name" class="mt-2 text-sm text-red-600">{{ errors.guest_name[0] }}</p>
                </div>

                <div>
                    <label for="comment" class="block font-medium text-sm text-gray-700">Comment</label>
                    <textarea
                        id="comment"
                        v-model="form.comment"
                        rows="3"
                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                    ></textarea>
                    <p v-if="errors.comment" class="mt-2 text-sm text-red-600">{{ errors.comment[0] }}</p>
                </div>

                <p v-if="formMessage" class="text-sm text-red-600">{{ formMessage }}</p>

                <button
                    type="submit"
                    :disabled="submitting"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50"
                >
                    {{ submitting ? 'Posting…' : 'Add comment' }}
                </button>
            </form>

            <div class="mt-6 space-y-4">
                <p v-if="post.comments.length === 0" class="text-gray-500">No comments yet.</p>

                <div v-for="comment in post.comments" :key="comment.id" class="border-t border-gray-100 pt-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-gray-500">
                            {{ comment.author_name }}
                            <span v-if="comment.is_guest" class="text-xs text-gray-400">(guest)</span>
                            · {{ formatDate(comment.created_at) }}
                        </p>
                        <button
                            v-if="comment.can.delete"
                            type="button"
                            class="text-xs text-red-600 hover:underline"
                            @click="deleteComment(comment)"
                        >
                            Delete
                        </button>
                    </div>
                    <p class="mt-1 text-gray-800 whitespace-pre-line">{{ comment.comment }}</p>
                </div>
            </div>
        </section>
    </div>
</template>