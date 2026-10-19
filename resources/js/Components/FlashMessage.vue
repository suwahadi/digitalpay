<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-2"
        >
            <div
                v-if="visible"
                class="fixed top-4 right-4 z-50 max-w-md w-full bg-white dark:bg-gray-800 border-l-4 rounded-lg shadow-lg"
                :class="[
                    typeClasses[type] || typeClasses.info,
                    { 'animate-pulse': isAutoRedirecting }
                ]"
            >
                <div class="p-4">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <IconifyIcon 
                                :icon="typeIcons[type] || typeIcons.info" 
                                width="20" 
                                :class="iconClasses[type] || iconClasses.info"
                            />
                        </div>
                        <div class="ml-3 flex-1">
                            <p class="text-sm font-medium text-gray-900 dark:text-white">
                                {{ message }}
                            </p>
                            <div v-if="isAutoRedirecting" class="mt-2">
                                <p class="text-xs text-gray-600 dark:text-gray-400">
                                    Mengalihkan dalam {{ timeRemaining }} detik...
                                </p>
                                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1 mt-1">
                                    <div 
                                        class="h-1 rounded-full transition-all duration-1000 ease-linear"
                                        :class="progressClasses[type] || progressClasses.info"
                                        :style="{ width: progressPercentage + '%' }"
                                    ></div>
                                </div>
                            </div>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <button
                                @click="close"
                                class="inline-flex text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                            >
                                <IconifyIcon icon="lucide:x" width="16" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import IconifyIcon from '@/Components/IconifyIcon.vue'

const page = usePage()
const visible = ref(false)
const timeRemaining = ref(0)
const totalTime = ref(0)
let redirectTimer = null

// Flash message data
const message = computed(() => {
    return page.props.flash?.error || 
           page.props.flash?.success || 
           page.props.flash?.warning || 
           page.props.flash?.info
})

const type = computed(() => {
    if (page.props.flash?.error) return 'error'
    if (page.props.flash?.success) return 'success'
    if (page.props.flash?.warning) return 'warning'
    return 'info'
})

const isAutoRedirecting = computed(() => {
    return page.props.flash?.autoRedirect && timeRemaining.value > 0
})

const progressPercentage = computed(() => {
    if (totalTime.value === 0) return 0
    return ((totalTime.value - timeRemaining.value) / totalTime.value) * 100
})

// Style configurations
const typeClasses = {
    error: 'border-red-400 bg-red-50 dark:bg-red-900/20',
    success: 'border-green-400 bg-green-50 dark:bg-green-900/20',
    warning: 'border-yellow-400 bg-yellow-50 dark:bg-yellow-900/20',
    info: 'border-blue-400 bg-blue-50 dark:bg-blue-900/20'
}

const iconClasses = {
    error: 'text-red-400',
    success: 'text-green-400',
    warning: 'text-yellow-400',
    info: 'text-blue-400'
}

const typeIcons = {
    error: 'lucide:x-circle',
    success: 'lucide:check-circle',
    warning: 'lucide:alert-triangle',
    info: 'lucide:info'
}

const progressClasses = {
    error: 'bg-red-400',
    success: 'bg-green-400',
    warning: 'bg-yellow-400',
    info: 'bg-blue-400'
}

const show = () => {
    if (!message.value) return
    
    visible.value = true
    
    // Handle auto-redirect
    if (page.props.flash?.autoRedirect && page.props.flash?.redirectDelay) {
        const delay = Math.round(page.props.flash.redirectDelay / 1000) // Convert to seconds
        timeRemaining.value = delay
        totalTime.value = delay
        
        redirectTimer = setInterval(() => {
            timeRemaining.value--
            
            if (timeRemaining.value <= 0) {
                clearInterval(redirectTimer)
                // Redirect to home
                router.visit('/')
            }
        }, 1000)
    } else {
        // Auto-hide after 5 seconds for regular messages
        setTimeout(() => {
            close()
        }, 5000)
    }
}

const close = () => {
    visible.value = false
    if (redirectTimer) {
        clearInterval(redirectTimer)
        redirectTimer = null
    }
}

onMounted(() => {
    show()
})

onUnmounted(() => {
    if (redirectTimer) {
        clearInterval(redirectTimer)
    }
})
</script>

<style scoped>
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.8;
    }
}
</style>