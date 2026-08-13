import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import vuetify from './Plugins/vuetify';
import Toast from './Plugins/toast';

const appName = import.meta.env.VITE_APP_NAME || 'OVMS Admin';

const initializeInertia = () => {
    const appElement = document.getElementById('app');
    const initialPage = appElement?.dataset.page ? JSON.parse(appElement.dataset.page) : null;

    if (!initialPage) {
        console.error('Inertia initial page was not found in the DOM.');
        return;
    }

    createInertiaApp({
        id: 'app',
        page: initialPage,
        title: (title) => `${title} - ${appName}`,
        resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
        setup({ el, App, props, plugin }) {
            return createApp({
                render: () => h(App, props),
            })
                .use(plugin)
                .use(ZiggyVue)
                .use(vuetify)
                .use(Toast)
                .mount(el);
        },
        progress: {
            color: '#4B5563',
        },
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeInertia, { once: true });
} else {
    initializeInertia();
}
