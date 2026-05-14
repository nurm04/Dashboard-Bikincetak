import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        // return createApp({ render: () => h(App, props) })
        //     .use(plugin)
        //     .use(ZiggyVue)
        //     .mount(el);

        const app = createApp({ render: () => h(App, props) });
        app.use(plugin)
           .use(ZiggyVue);
        app.config.globalProperties.$can = function (modul, aksi = 'buka') {
            const permissions = this.$page.props.auth?.can;

            if (!permissions || !permissions[modul]) {
                return false;
            }

            return permissions[modul][aksi] ?? false;
        };

        return app.mount(el);
    },
    progress: {
        color: '#3885f8',
    },
});
