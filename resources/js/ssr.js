import { createSSRApp, h } from 'vue'
import { renderToString } from '@vue/server-renderer'
import { createInertiaApp } from '@inertiajs/vue3'
import createServer from '@inertiajs/vue3/server'
import IconifyIcon from './Components/IconifyIcon.vue'
import { route } from './routes'

const pages = import.meta.glob('./Pages/**/*.vue')

createServer(page =>
    createInertiaApp({
        page,
        render: renderToString,
        title: title => title ? `${title} - DigitalPay` : 'DigitalPay',
        resolve: (name) => pages[`./Pages/${name}.vue`](),
        setup({ App, props, plugin }) {
            const app = createSSRApp({ render: () => h(App, props) })
                .use(plugin)
                .component('IconifyIcon', IconifyIcon)
                .provide('route', route)

            app.config.globalProperties.route = route

            return app
        },
    })
)
