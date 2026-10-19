import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import IconifyIcon from './Components/IconifyIcon.vue'
import { route } from './routes'
import '../css/app.css'

createInertiaApp({
    title: title => title ? `${title} - DigitalPay` : 'DigitalPay',
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue')
        return pages[`./Pages/${name}.vue`]()
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .component('IconifyIcon', IconifyIcon)
            .provide('route', route)

        app.config.globalProperties.route = route

        return app.mount(el)
    },
    progress: {
        color: '#3b82f6',
    },
})
