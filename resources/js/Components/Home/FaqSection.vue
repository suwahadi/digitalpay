<template>
    <section class="py-16 bg-white dark:bg-gray-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    Pertanyaan yang Sering Diajukan
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400">
                    Temukan jawaban untuk pertanyaan umum seputar layanan kami
                </p>
            </div>

            <div class="space-y-4">
                <div 
                    v-for="faq in faqs" 
                    :key="faq.id"
                    class="border border-gray-200 dark:border-gray-600 rounded-lg overflow-hidden transition-all duration-300 hover:shadow-md"
                >
                    <button
                        @click="toggleFaq(faq.id)"
                        class="w-full px-6 py-4 text-left bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-all duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                    >
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white pr-4">
                                {{ faq.question }}
                            </h3>
                            <IconifyIcon 
                                icon="lucide:chevron-down" 
                                width="20"
                                :class="{ 'rotate-180': openFaqs.includes(faq.id) }"
                                class="transition-all duration-500 ease-in-out text-gray-500 dark:text-gray-400 flex-shrink-0"
                            />
                        </div>
                    </button>
                    
                    <Transition
                        name="faq-slide"
                        enter-active-class="transition-all duration-500 ease-in-out"
                        leave-active-class="transition-all duration-400 ease-in-out"
                        enter-from-class="opacity-0 max-h-0"
                        enter-to-class="opacity-100 max-h-96"
                        leave-from-class="opacity-100 max-h-96"
                        leave-to-class="opacity-0 max-h-0"
                    >
                        <div 
                            v-if="openFaqs.includes(faq.id)"
                            class="overflow-hidden"
                        >
                            <div class="px-6 py-4 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-600">
                                <div class="animate-fade-in">
                                    <p class="text-gray-600 dark:text-gray-400 leading-relaxed" v-html="faq.answer"></p>
                                </div>
                            </div>
                        </div>
                    </Transition>
                </div>
            </div>

            <div class="text-center mt-8">
                <Link
                    href="/faq"
                    class="btn btn-secondary"
                >
                    Lihat FAQ Lengkap
                </Link>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import IconifyIcon from '../IconifyIcon.vue'

defineProps({
    faqs: {
        type: Array,
        default: () => [],
        required: true
    }
})

const openFaqs = ref([])

const toggleFaq = (faqId) => {
    openFaqs.value = openFaqs.value.includes(faqId) ? [] : [faqId]
}
</script>

<style scoped>
.faq-slide-enter-active,
.faq-slide-leave-active {
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
    transform-origin: top;
}

.faq-slide-enter-from {
    opacity: 0;
    max-height: 0;
    transform: translateY(-10px) scaleY(0.95);
}

.faq-slide-enter-to {
    opacity: 1;
    max-height: 500px;
    transform: translateY(0) scaleY(1);
}

.faq-slide-leave-from {
    opacity: 1;
    max-height: 500px;
    transform: translateY(0) scaleY(1);
}

.faq-slide-leave-to {
    opacity: 0;
    max-height: 0;
    transform: translateY(-5px) scaleY(0.98);
}

/* Content Fade In Animation */
@keyframes fade-in {
    0% {
        opacity: 0;
        transform: translateY(8px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in {
    animation: fade-in 0.6s ease-out 0.1s both;
}

/* Enhanced Button Hover Effects */
button:hover .text-gray-500 {
    color: rgb(107 114 128);
    transform: scale(1.05);
}

.dark button:hover .text-gray-400 {
    color: rgb(156 163 175);
    transform: scale(1.05);
}

/* Smooth border transitions */
.border {
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

.border:hover {
    border-color: rgb(209 213 219);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.dark .border:hover {
    border-color: rgb(75 85 99);
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3), 0 2px 4px -1px rgba(0, 0, 0, 0.2);
}
</style>