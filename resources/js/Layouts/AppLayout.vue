<template>
    <div class="bg-gray-50 dark:bg-bluegray min-h-screen flex flex-col">
        <!-- Preloader -->
        <Preloader :show="isLoading" />
        
        <!-- Header -->
        <Header />
        
        <!-- Main Content -->
        <main class="flex-1">
            <slot />
        </main>
        
        <!-- Footer -->
        <Footer />

        <!-- Flash Messages -->
        <FlashMessage />

        <!-- Toast Notifications -->
        <ToastNotification />
    </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'
import Header from '@/Components/Layout/Header.vue'
import Footer from '@/Components/Layout/Footer.vue'
import Preloader from '@/Components/Layout/Preloader.vue'
import FlashMessage from '@/Components/FlashMessage.vue'
import ToastNotification from '@/Components/ToastNotification.vue'

defineProps({
    // settings: Object,
    // categories: Array,
})

const isLoading = ref(true)

onMounted(async () => {
    // Initial page load - show preloader for a minimum time
    const minLoadTime = 1500 // 1.5 seconds minimum
    const startTime = Date.now()
    
    // Wait for initial render
    await nextTick()
    
    // Calculate remaining time to reach minimum load time
    const elapsedTime = Date.now() - startTime
    const remainingTime = Math.max(0, minLoadTime - elapsedTime)
    
    setTimeout(() => {
        isLoading.value = false
    }, remainingTime)
    
    // Handle navigation loading
    router.on('start', () => {
        isLoading.value = true
    })
    
    router.on('finish', () => {
        // Add small delay for smooth transition
        setTimeout(() => {
            isLoading.value = false
        }, 300)
    })
})
</script>
