<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../api';

const props = defineProps({
    postId: { type: Number, default: null },
});

const isEdit = props.postId !== null;

const form = reactive({ title: '', content: '' });
const errors = ref({});
const loading = ref(isEdit);
const submitting = ref(false);
const message = ref(null);

onMounted(async () => {
    if (!isEdit) return;

    try {
        const { data } = await api.get(`/posts/${props.postId}`);
        form.title = data.data.title;
        form.content = data.data.content;
    } catch {
        message.value = 'Could not load the post.';
    } finally {
        loading.value = false;
    }
});

async function submit() {
    submitting.value = true;
    errors.value = {};
    message.value = null;

    try {
        const { data } = isEdit
            ? await api.put(`/posts/${props.postId}`, form)
            : await api.post('/posts', form);

        window.location.href = `/posts/${data.data.id}`;
    } catch (e) {
        const status = e.response?.status;

        if (status === 422) {
            errors.value = e.response.data.errors;
        } else if (status === 401) {
            window.location.href = '/login';
        } else if (status === 403) {
            message.value = 'You are not allowed to edit this post.';
        } else {
            message.value = 'Something went wrong. Please try again.';
        }

        submitting.value = false;
    }
}
</script>

<template>
    <p v-if="loading" class="text-gray-500">Loading…</p>

    <form v-else @submit.prevent="submit">
        <div>
            <label for="title" class="block font-medium text-sm text-gray-700">Title</label>
            <input
                id="title"
                v-model="form.title"
                type="text"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
            >
            <p v-if="errors.title" class="mt-2 text-sm text-red-600">{{ errors.title[0] }}</p>
        </div>

        <div class="mt-4">
            <label for="content" class="block font-medium text-sm text-gray-700">Content</label>
            <textarea
                id="content"
                v-model="form.content"
                rows="10"
                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
            ></textarea>
            <p v-if="errors.content" class="mt-2 text-sm text-red-600">{{ errors.content[0] }}</p>
        </div>

        <p v-if="message" class="mt-4 text-sm text-red-600">{{ message }}</p>

        <div class="mt-6 flex items-center gap-4">
            <button
                type="submit"
                :disabled="submitting"
                class="inline-flex items-center px-4 py-2 bg-gray-800 rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 disabled:opacity-50"
            >
                {{ isEdit ? 'Save changes' : 'Publish' }}
            </button>
            <a :href="isEdit ? `/posts/${postId}` : '/posts'" class="text-sm text-gray-600 hover:underline">Cancel</a>
        </div>
    </form>
</template>