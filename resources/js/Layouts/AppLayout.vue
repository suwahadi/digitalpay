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
import ToastNotification from '@/Components/ToastNotification.vue'

defineProps({})

const isLoading = ref(true)

onMounted(async () => {
    const minLoadTime = 1500
    const startTime = Date.now()
    
    await nextTick()
    
    const elapsedTime = Date.now() - startTime
    const remainingTime = Math.max(0, minLoadTime - elapsedTime)
    
    setTimeout(() => {
        isLoading.value = false
    }, remainingTime)
    
    router.on('start', () => {
        isLoading.value = true
    })
    
    router.on('finish', () => {
        setTimeout(() => {
            isLoading.value = false
        }, 300)
    })
})
</script>
