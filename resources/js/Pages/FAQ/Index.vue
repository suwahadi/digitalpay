<template>
    <AppLayout :settings="settings">
        <Head title="FAQ" />

        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 pt-20 pb-12">
            <!-- Hero Section -->
            <div class="bg-white dark:bg-gray-800">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
                    <!-- Breadcrumb -->
                    <nav class="mb-8">
                        <ol class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                            <li>
                                <Link href="/" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    Beranda
                                </Link>
                            </li>
                            <li class="text-gray-400">/</li>
                            <li class="text-gray-900 dark:text-white">
                                FAQ
                            </li>
                        </ol>
                    </nav>

                    <!-- Page Header -->
                    <div class="text-center mb-12">
                        <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white mb-4">
                            Pertanyaan yang Sering Diajukan
                        </h1>
                        <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                            Temukan jawaban untuk pertanyaan umum seputar layanan kami. 
                            Jika tidak menemukan jawaban yang Anda cari, jangan ragu untuk menghubungi kami
                        </p>
                    </div>

                    <!-- Search and Filter Section -->
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-8">
                        <div class="flex flex-col lg:flex-row gap-4">
                            <!-- Search Input -->
                            <div class="flex-1">
                                <div class="relative">
                                    <IconifyIcon 
                                        icon="lucide:search" 
                                        width="20" 
                                        class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"
                                    />
                                    <input
                                        v-model="searchTerm"
                                        @input="handleSearch"
                                        type="text"
                                        placeholder="Cari pertanyaan atau jawaban..."
                                        aria-label="Cari pertanyaan"
                                        class="w-full pl-10 pr-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                    />
                                </div>
                            </div>

                            <!-- Category Filter -->
                            <div class="lg:w-64">
                                <select
                                    v-model="selectedCategory"
                                    @change="handleCategoryChange"
                                    aria-label="Kategori pertanyaan"
                                    class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                >
                                    <option value="">Semua Kategori</option>
                                    <option 
                                        v-for="category in categories" 
                                        :key="category" 
                                        :value="category"
                                    >
                                        {{ category }}
                                    </option>
                                </select>
                            </div>

                            <!-- Clear Filters -->
                            <button
                                v-if="searchTerm || selectedCategory"
                                @click="clearFilters"
                                class="px-6 py-3 bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-500 transition-colors flex items-center gap-2"
                            >
                                <IconifyIcon icon="lucide:x" width="16" />
                                Reset
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Content -->
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Loading Skeleton -->
                <div v-if="isLoading" class="space-y-4">
                    <div 
                        v-for="n in 5" 
                        :key="`skeleton-${n}`"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6"
                    >
                        <div class="flex items-center justify-between mb-4">
                            <div class="w-3/4 h-6 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                            <div class="w-6 h-6 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                        </div>
                    </div>
                </div>

                <!-- FAQ List -->
                <div v-else-if="filteredFaqs.length > 0" class="space-y-4">
                    <div 
                        v-for="faq in filteredFaqs" 
                        :key="faq.id"
                        class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden transition-all duration-300 hover:shadow-md"
                    >
                        <button
                            @click="toggleFaq(faq.id)"
                            class="w-full px-6 py-6 text-left hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-300 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                        >
                            <div class="flex items-start justify-between">
                                <div class="flex-1 pr-4">
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                        {{ faq.question }}
                                    </h2>
                                    <div v-if="faq.category" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                        {{ faq.category }}
                                    </div>
                                </div>
                                <span
                                    class="flex transition-transform duration-500 ease-in-out flex-shrink-0 mt-1"
                                    :class="{ 'rotate-180': openFaqs.includes(faq.id) }"
                                >
                                    <IconifyIcon icon="lucide:chevron-down" width="24" class="text-gray-400 dark:text-gray-500" />
                                </span>
                            </div>
                        </button>
                        
                        <Transition
                            name="faq-slide"
                            enter-active-class="transition-all duration-500 ease-in-out"
                            leave-active-class="transition-all duration-400 ease-in-out"
                            enter-from-class="opacity-0 max-h-0"
                            enter-to-class="opacity-100 max-h-[600px]"
                            leave-from-class="opacity-100 max-h-[600px]"
                            leave-to-class="opacity-0 max-h-0"
                        >
                            <div 
                                v-if="openFaqs.includes(faq.id)"
                                class="overflow-hidden"
                            >
                                <div class="px-6 py-6 bg-gray-50 dark:bg-gray-700 border-t border-gray-100 dark:border-gray-600">
                                    <div class="animate-fade-in">
                                        <div class="prose dark:prose-invert max-w-none">
                                            <div 
                                                class="text-gray-700 dark:text-gray-300 leading-relaxed" 
                                                v-html="faq.answer"
                                            ></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>

                <!-- No Results -->
                <div v-else class="text-center py-16">
                    <IconifyIcon icon="lucide:help-circle" width="64" class="mx-auto text-gray-400 dark:text-gray-500 mb-4" />
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">
                        Tidak Ada FAQ Ditemukan
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-8">
                        Coba ubah kata kunci pencarian atau kategori yang dipilih.
                    </p>
                    <button
                        @click="clearFilters"
                        class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        Tampilkan Semua FAQ
                    </button>
                </div>

                <!-- Contact Section -->
                <div class="mt-16 bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6 text-center sm:p-8">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                        Masih Ada Pertanyaan?
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6">
                        Tim dukungan kami siap membantu Anda. Jangan ragu untuk menghubungi kami.
                    </p>
                    <div class="mx-auto grid max-w-md grid-cols-2 gap-3 sm:gap-4">
                        <a
                            :href="`mailto:${$page.props.settings?.contact?.email || 'admin@digitalpay.web.id'}`"
                            class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-lg bg-blue-600 px-2 py-3 text-sm font-medium text-white transition-colors hover:bg-blue-700 sm:px-6 sm:text-base"
                        >
                            <IconifyIcon icon="lucide:mail" width="18" class="shrink-0" />
                            Email Support
                        </a>
                        <a
                            :href="`https://wa.me/${$page.props.settings?.contact?.whatsapp || '6281234567890'}`"
                            target="_blank"
                            rel="noopener"
                            class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-lg bg-green-700 px-2 py-3 text-sm font-medium text-white transition-colors hover:bg-green-800 sm:px-6 sm:text-base"
                        >
                            <IconifyIcon icon="simple-icons:whatsapp" width="18" class="shrink-0" />
                            WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import IconifyIcon from '@/Components/IconifyIcon.vue'
import debounce from 'lodash/debounce'

const props = defineProps({
    faqs: Array,
    categories: Array,
    title: String,
    settings: Object,
    filters: Object,
})

const isLoading = ref(true)
const openFaqs = ref([])
const searchTerm = ref(props.filters?.search || '')
const selectedCategory = ref(props.filters?.category || '')

const filteredFaqs = computed(() => {
    let faqs = props.faqs || []
    
    if (searchTerm.value) {
        const search = searchTerm.value.toLowerCase()
        faqs = faqs.filter(faq => 
            faq.question.toLowerCase().includes(search) || 
            faq.answer.toLowerCase().includes(search)
        )
    }
    
    if (selectedCategory.value) {
        faqs = faqs.filter(faq => faq.category === selectedCategory.value)
    }
    
    return faqs
})

const toggleFaq = (faqId) => {
    openFaqs.value = openFaqs.value.includes(faqId) ? [] : [faqId]
}

const handleSearch = debounce(() => {
    updateFilters()
}, 300)

const handleCategoryChange = () => {
    updateFilters()
}

const updateFilters = () => {
    const params = {}
    
    if (searchTerm.value) {
        params.search = searchTerm.value
    }
    
    if (selectedCategory.value) {
        params.category = selectedCategory.value
    }
    
    router.get('/faq', params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

const clearFilters = () => {
    searchTerm.value = ''
    selectedCategory.value = ''
    router.get('/faq', {}, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}

onMounted(() => {
    setTimeout(() => {
        isLoading.value = false
    }, 600)
})
</script>

<style scoped>
/* FAQ Slide Animation */
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
    max-height: 600px;
    transform: translateY(0) scaleY(1);
}

.faq-slide-leave-from {
    opacity: 1;
    max-height: 600px;
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
        transform: translateY(12px);
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
button:hover .text-gray-400 {
    color: rgb(107 114 128);
    transform: scale(1.05);
}

.dark button:hover .text-gray-500 {
    color: rgb(156 163 175);
    transform: scale(1.05);
}

/* Smooth transitions for cards */
.bg-white,
.dark .bg-gray-800 {
    transition: all 0.3s ease;
}

.hover\:shadow-md:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

.dark .hover\:shadow-md:hover {
    box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
}

/* Prose styling for FAQ answers */
.prose {
    line-height: 1.7;
}

.prose p {
    margin-bottom: 1rem;
}

.prose ul,
.prose ol {
    margin: 1rem 0;
    padding-left: 1.5rem;
}

.prose li {
    margin: 0.25rem 0;
}

.prose strong {
    font-weight: 600;
}

.prose a {
    color: #2563eb;
    text-decoration: underline;
}

.prose a:hover {
    color: #1d4ed8;
}

.dark .prose a {
    color: #60a5fa;
}

.dark .prose a:hover {
    color: #93c5fd;
}
</style>