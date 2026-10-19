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
import { ref, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import Header from '@/Components/Layout/Header.vue'
import Footer from '@/Components/Layout/Footer.vue'
import Preloader from '@/Components/Layout/Preloader.vue'
import ToastNotification from '@/Components/ToastNotification.vue'

defineProps({})

/**
 * The skeleton only covers real page-to-page navigations that are still
 * pending after SHOW_DELAY ms. Form posts, filter/search visits that keep
 * state and partial reloads stay on the page (the top progress bar and the
 * buttons' own spinners cover those), and nothing is shown on first load
 * because the page is already rendered.
 */
const SHOW_DELAY = 250

const isLoading = ref(false)
let showTimer = null
const unsubscribers = []

const isPageNavigation = (visit) =>
    visit.method === 'get' && !visit.preserveState && !visit.only?.length && !visit.except?.length

onMounted(() => {
    unsubscribers.push(
        router.on('start', (event) => {
            if (!isPageNavigation(event.detail.visit)) return
            clearTimeout(showTimer)
            showTimer = setTimeout(() => {
                isLoading.value = true
            }, SHOW_DELAY)
        }),
        router.on('finish', () => {
            clearTimeout(showTimer)
            isLoading.value = false
        }),
    )
})

onUnmounted(() => {
    clearTimeout(showTimer)
    unsubscribers.forEach((off) => off())
})
</script>
