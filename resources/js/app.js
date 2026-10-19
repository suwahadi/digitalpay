import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import IconifyIcon from './Components/IconifyIcon.vue'
import '../css/app.css'

function route(name, params = {}) {
    const routes = window.routes || {}
    let url = routes[name]
    
    if (!url) {
        console.warn(`Route '${name}' not found. Available routes:`, Object.keys(routes))
        return `/${name}`
    }

    let finalUrl = url
    for (const [key, value] of Object.entries(params)) {
        const encodedValue = encodeURIComponent(value)
        finalUrl = finalUrl.replace(`{${key}}`, encodedValue)
        finalUrl = finalUrl.replace(`{${key}:slug}`, encodedValue)
    }
    
    return finalUrl
}

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
