

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();


import { createApp } from 'vue';
import PostList from './components/PostList.vue';
import PostShow from './components/PostShow.vue';
import PostForm from './components/PostForm.vue';

const components = {
    'post-list': PostList,
    'post-show': PostShow,
    'post-form': PostForm,
};

document.querySelectorAll('[data-vue]').forEach((el) => {
    const component = components[el.dataset.vue];
    if (!component) return;

    const props = el.dataset.props ? JSON.parse(el.dataset.props) : {};
    createApp(component, props).mount(el);
});