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
        const vueApp = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // Add global currency helpers
        vueApp.config.globalProperties.$currency = (value) => {
    if (!value) return '₵0.00';
    return new Intl.NumberFormat('en-GH', {
        style: 'currency',
        currency: 'GHS',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(value);
        };

        vueApp.config.globalProperties.$formatPrice = (value) => {
    if (!value) return '0.00';
    return parseFloat(value).toFixed(2);
        };

        vueApp.config.globalProperties.$calculateMargin = (cost, selling) => {
            const costNum = parseFloat(cost) || 0;
            const sellingNum = parseFloat(selling) || 0;
            
            if (costNum === 0) return 100;
            return ((sellingNum - costNum) / costNum) * 100;
        };



        return vueApp.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});