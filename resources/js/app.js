import './bootstrap';
import '../css/app.css';

import 'primevue/resources/themes/saga-blue/theme.css';
import 'primevue/resources/primevue.css';
import 'primeicons/primeicons.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';
import { InertiaProgress } from '@inertiajs/progress';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy/dist/vue.m';
import PrimeVue from 'primevue/config';
import Dialog
    from "primevue/dialog";
import Button
    from "primevue/button";
import FileUpload
    from "primevue/fileupload";
import ProgressBar
    from "primevue/progressbar";
import ConfirmationService
    from "primevue/confirmationservice";
import ConfirmDialog
    from "primevue/confirmdialog";

const appName = window.document.getElementsByTagName('title')[0]?.innerText || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, app, props, plugin }) {
        return createApp({ render: () => h(app, props) })
            .use(plugin)
            .use(ZiggyVue, Ziggy)
            .use(PrimeVue)
            .use(ConfirmationService)
            .component('Dialog', Dialog)
            .component('Button', Button)
            .component('FileUpload', FileUpload)
            .component('ProgressBar', ProgressBar)
            .component('ConfirmDialog', ConfirmDialog)
            .mount(el);
    },
});

InertiaProgress.init({ color: '#4B5563' });
