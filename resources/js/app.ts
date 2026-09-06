import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';

createInertiaApp({
    title: (title) => {
        if (!title) return 'SIMPEL DBI';
        const cleanTitle = title.replace(/\s*[\u2014-]\s*SIMPEL DBI/gi, '').trim();
        return cleanTitle ? `${cleanTitle} - SIMPEL DBI` : 'SIMPEL DBI';
    },

    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./Pages/**/*.vue'),
        ),

    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },

    progress: {
        color: 'oklch(0.329 0.099 253.2)', // --primary (navy)
        delay: 250,
    },
});
