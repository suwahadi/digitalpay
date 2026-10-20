<template>
    <div
        class="fixed top-4 left-4 right-4 z-[70] flex flex-col items-end gap-3 pointer-events-none sm:left-auto sm:w-full sm:max-w-sm"
        aria-label="Notifikasi"
    >
        <transition-group name="toast">
            <div
                v-for="toast in toasts"
                :key="toast.id"
                :role="toast.type === 'error' ? 'alert' : 'status'"
                :class="['pointer-events-auto flex w-full items-start gap-3 rounded-lg px-5 py-4 text-white shadow-lg', backgrounds[toast.type] || backgrounds.info]"
            >
                <component :is="icons[toast.type] || icons.info" class="mt-0.5 h-5 w-5 shrink-0" aria-hidden="true" />
                <div class="min-w-0 flex-1">
                    <p class="font-medium leading-snug">{{ toast.title }}</p>
                    <p v-if="toast.message" class="mt-1 text-sm leading-relaxed text-white/90">{{ toast.message }}</p>
                </div>
                <button
                    type="button"
                    class="-m-1.5 shrink-0 rounded-md p-1.5 text-white/80 transition-colors hover:bg-white/15 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-white"
                    aria-label="Tutup notifikasi"
                    @click="removeToast(toast.id)"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </transition-group>
    </div>
</template>

<script setup>
import { ref, h, watch, onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'

const icon = (d) => ({
    render: () => h('svg', { fill: 'none', viewBox: '0 0 24 24', stroke: 'currentColor' }, [
        h('path', { 'stroke-linecap': 'round', 'stroke-linejoin': 'round', 'stroke-width': '2', d }),
    ]),
})

const icons = {
    success: icon('M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'),
    error: icon('M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'),
    warning: icon('M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'),
    info: icon('M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'),
}

const backgrounds = {
    success: 'bg-green-700',
    error: 'bg-red-600',
    warning: 'bg-amber-700',
    info: 'bg-blue-600',
}

const page = usePage()
const toasts = ref([])
const timers = new Map()

const removeToast = (id) => {
    clearTimeout(timers.get(id))
    timers.delete(id)
    toasts.value = toasts.value.filter((toast) => toast.id !== id)
}

const addToast = ({ type = 'info', title, message, duration }) => {
    const id = Date.now() + Math.random()
    toasts.value.push({ id, type, title, message })
    timers.set(id, setTimeout(() => removeToast(id), duration || 4000))
}

if (typeof window !== 'undefined') {
    window.toast = {
        success: (title, message, duration) => addToast({ type: 'success', title, message, duration }),
        error: (title, message, duration) => addToast({ type: 'error', title, message, duration }),
        warning: (title, message, duration) => addToast({ type: 'warning', title, message, duration }),
        info: (title, message, duration) => addToast({ type: 'info', title, message, duration }),
    }
}

const flashDurations = {
    error: 6000,
    warning: 6000,
    info: 4000,
    success: 4000,
}

/**
 * Server flashes (redirect()->with('error', ...)) shown as toasts. `message` is
 * left to the pages that show it themselves (auth pages, profile).
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
    timers.forEach(clearTimeout)
    timers.clear()
})
</script>

<style scoped>
.toast-enter-active {
    transition: opacity 0.25s ease-out, transform 0.25s ease-out;
}

.toast-leave-active {
    transition: opacity 0.18s ease-in, transform 0.18s ease-in;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>
