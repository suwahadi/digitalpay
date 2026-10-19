<template>
    <div class="fixed top-4 right-4 z-50 space-y-3 max-w-md w-full sm:max-w-sm">
        <transition-group
            name="toast"
            tag="div"
            class="space-y-3"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                :class="[
                    'w-full bg-white dark:bg-gray-800 shadow-xl rounded-lg pointer-events-auto overflow-hidden border-l-4',
                    getToastClasses(toast.type)
                ]"
            >
                <div class="p-5">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <component :is="getIcon(toast.type)" :class="getIconClasses(toast.type)" />
                        </div>
                        <div class="ml-4 flex-1 min-w-0">
                            <p :class="['text-base font-semibold leading-5', getTextClasses(toast.type)]">
                                {{ toast.title }}
                            </p>
                            <p v-if="toast.message" :class="['mt-2 text-sm leading-relaxed', getSubTextClasses(toast.type)]">
                                {{ toast.message }}
                            </p>
                        </div>
                        <div class="ml-3 flex-shrink-0 flex">
                            <button
                                @click="removeToast(toast.id)"
                                :class="[
                                    'rounded-full p-1 inline-flex text-gray-400 hover:text-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 transition-colors duration-200',
                                    getCloseButtonClasses(toast.type)
                                ]"
                            >
                                <span class="sr-only">Close</span>
                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Progress bar -->
                <div :class="['h-1.5', getProgressBarBg(toast.type)]">
                    <div
                        :class="['h-full transition-all ease-linear rounded-full', getProgressBarColor(toast.type)]"
                        :style="{ width: `${toast.progress}%` }"
                    ></div>
                </div>
            </div>
        </transition-group>
    </div>
</template>

<script setup>
import { ref, h, watch, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

const CheckCircleIcon = {
    render() {
        return h('svg', {
            class: 'h-6 w-6',
            fill: 'none',
            viewBox: '0 0 24 24',
            stroke: 'currentColor'
        }, [
            h('path', {
                'stroke-linecap': 'round',
                'stroke-linejoin': 'round',
                'stroke-width': '2',
                d: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
            })
        ])
    }
}

const ExclamationCircleIcon = {
    render() {
        return h('svg', {
            class: 'h-6 w-6',
            fill: 'none',
            viewBox: '0 0 24 24',
            stroke: 'currentColor'
        }, [
            h('path', {
                'stroke-linecap': 'round',
                'stroke-linejoin': 'round',
                'stroke-width': '2',
                d: 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
            })
        ])
    }
}

const XCircleIcon = {
    render() {
        return h('svg', {
            class: 'h-6 w-6',
            fill: 'none',
            viewBox: '0 0 24 24',
            stroke: 'currentColor'
        }, [
            h('path', {
                'stroke-linecap': 'round',
                'stroke-linejoin': 'round',
                'stroke-width': '2',
                d: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'
            })
        ])
    }
}

const InformationCircleIcon = {
    render() {
        return h('svg', {
            class: 'h-6 w-6',
            fill: 'none',
            viewBox: '0 0 24 24',
            stroke: 'currentColor'
        }, [
            h('path', {
                'stroke-linecap': 'round',
                'stroke-linejoin': 'round',
                'stroke-width': '2',
                d: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'
            })
        ])
    }
}

const page = usePage()
const toasts = ref([])
const timers = new Set()

const addToast = (toast) => {
    const id = Date.now() + Math.random()
    const newToast = {
        id,
        progress: 100,
        ...toast
    }
    
    toasts.value.push(newToast)
    
    const duration = toast.duration || 3000
    const interval = 50
    const steps = duration / interval
    const progressStep = 100 / steps
    
    const timer = setInterval(() => {
        newToast.progress -= progressStep
        if (newToast.progress <= 0) {
            clearInterval(timer)
            timers.delete(timer)
            removeToast(id)
        }
    }, interval)
    timers.add(timer)
}

const removeToast = (id) => {
    const index = toasts.value.findIndex(toast => toast.id === id)
    if (index > -1) {
        toasts.value.splice(index, 1)
    }
}

const getIcon = (type) => {
    const icons = {
        success: CheckCircleIcon,
        error: XCircleIcon,
        warning: ExclamationCircleIcon,
        info: InformationCircleIcon
    }
    return icons[type] || InformationCircleIcon
}

const getToastClasses = (type) => {
    const classes = {
        success: 'border-green-400 dark:border-green-500',
        error: 'border-red-400 dark:border-red-500',
        warning: 'border-yellow-400 dark:border-yellow-500',
        info: 'border-blue-400 dark:border-blue-500'
    }
    return classes[type] || classes.info
}

const getIconClasses = (type) => {
    const classes = {
        success: 'text-green-400',
        error: 'text-red-400',
        warning: 'text-yellow-400',
        info: 'text-blue-400'
    }
    return classes[type] || classes.info
}

const getTextClasses = (type) => {
    const classes = {
        success: 'text-gray-900 dark:text-white',
        error: 'text-gray-900 dark:text-white',
        warning: 'text-gray-900 dark:text-white',
        info: 'text-gray-900 dark:text-white'
    }
    return classes[type] || classes.info
}

const getSubTextClasses = (type) => {
    const classes = {
        success: 'text-gray-600 dark:text-gray-300',
        error: 'text-gray-600 dark:text-gray-300',
        warning: 'text-gray-600 dark:text-gray-300',
        info: 'text-gray-600 dark:text-gray-300'
    }
    return classes[type] || classes.info
}

const getCloseButtonClasses = (type) => {
    const classes = {
        success: 'hover:bg-green-50 focus:ring-green-500',
        error: 'hover:bg-red-50 focus:ring-red-500',
        warning: 'hover:bg-yellow-50 focus:ring-yellow-500',
        info: 'hover:bg-blue-50 focus:ring-blue-500'
    }
    return classes[type] || classes.info
}

const getProgressBarBg = (type) => {
    const classes = {
        success: 'bg-green-100 dark:bg-green-900/20',
        error: 'bg-red-100 dark:bg-red-900/20',
        warning: 'bg-yellow-100 dark:bg-yellow-900/20',
        info: 'bg-blue-100 dark:bg-blue-900/20'
    }
    return classes[type] || classes.info
}

const getProgressBarColor = (type) => {
    const classes = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    }
    return classes[type] || classes.info
}

if (typeof window !== 'undefined') {
    window.toast = {
        success: (title, message, duration) => addToast({ type: 'success', title, message, duration }),
        error: (title, message, duration) => addToast({ type: 'error', title, message, duration }),
        warning: (title, message, duration) => addToast({ type: 'warning', title, message, duration }),
        info: (title, message, duration) => addToast({ type: 'info', title, message, duration })
    }
}

const flashDurations = {
    error: 6000,
    warning: 6000,
    info: 4000,
    success: 4000
}

/**
 * Server flashes (redirect()->with('error', ...)) shown as toasts. `message` is
 * left to the auth pages, which render their own notice for it.
 */
const showFlash = (flash) => {
    if (!flash) return
    Object.keys(flashDurations).forEach((type) => {
        if (flash[type]) {
            addToast({ type, title: flash[type], duration: flashDurations[type] })
        }
    })
}

onMounted(() => {
    showFlash(page.props.flash)
})

watch(() => page.props.flash, showFlash)

onUnmounted(() => {
    timers.forEach(clearInterval)
    timers.clear()
})
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}
</style>