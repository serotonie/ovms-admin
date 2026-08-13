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
    const pageScript = document.querySelector('script[data-page]');
    let initialPage = null;

    if (pageScript) {
        try {
            initialPage = JSON.parse(pageScript.textContent);
        } catch (error) {
            console.error('Unable to parse Inertia initial page payload.', error);
        }
    }

    if (!initialPage && appElement?.dataset.page) {
        try {
            initialPage = JSON.parse(appElement.dataset.page);
        } catch (error) {
            console.error('Unable to parse Inertia initial page from app element.', error);
        }
    }

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
