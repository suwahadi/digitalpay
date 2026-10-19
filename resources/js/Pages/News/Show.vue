<template>
    <AppLayout :settings="settings">
        <Head :title="article.title" />

        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 pt-20 pb-12">
            <!-- Article Content Container -->
            <div class="w-full mx-auto max-w-4xl py-12 px-2 sm:px-4">
                <div class="rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 sm:p-8">
                    <!-- Breadcrumb -->
                    <nav class="mb-6">
                        <ol class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                            <li>
                                <Link href="/" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    Home
                                </Link>
                            </li>
                            <li class="text-gray-400">/</li>
                            <li>
                                <Link href="/news" class="hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    Berita
                                </Link>
                            </li>
                            <li class="text-gray-400">/</li>
                            <li class="text-gray-900 dark:text-white truncate">{{ article.title }}</li>
                        </ol>
                    </nav>

                    <!-- Article Header -->
                    <div class="mb-8">
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                            {{ article.title }}
                        </h1>
                        
                        <div class="flex flex-wrap items-center gap-3 sm:gap-4 text-sm text-gray-500 dark:text-gray-400">
                            <div class="flex items-center">
                                <IconifyIcon icon="lucide:calendar" width="16" class="mr-1" />
                                {{ article.published_date }}
                            </div>
                            <div v-if="article.category" class="flex items-center">
                                <IconifyIcon icon="lucide:tag" width="16" class="mr-1" />
                                {{ capitalizeFirst(article.category) }}
                            </div>
                        </div>
                        
                        <p v-if="article.excerpt" class="text-base sm:text-lg text-gray-600 dark:text-gray-300 mt-4">
                            {{ article.excerpt }}
                        </p>
                    </div>

                    <!-- Featured Image -->
                    <div v-if="article.featured_image || article.featured_image_url" class="mb-8">
                        <img
                            :src="getImageUrl(article.featured_image || article.featured_image_url)"
                            :alt="article.title"
                            class="w-full h-48 sm:h-64 lg:h-80 object-cover rounded-lg shadow-lg"
                            @error="onImageError($event, '/images/placeholder.png')"
                        />
                    </div>

                    <!-- Article Content -->
                    <div class="mb-8">
                        <div 
                            class="prose dark:prose-invert max-w-none prose-blue prose-lg prose-content"
                            v-html="article.content"
                        ></div>
                    </div>

                    <!-- Share Section -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 sm:pt-6">
                        <h3 class="text-sm sm:text-base font-semibold text-gray-900 dark:text-white mb-2 sm:mb-3">
                            Bagikan Artikel
                        </h3>
                        <div class="grid grid-cols-4 gap-2">
                            <a
                                :href="`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(currentUrl)}`"
                                target="_blank"
                                class="flex items-center justify-center px-2 py-2 sm:px-3 sm:py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-xs sm:text-sm"
                                title="Bagikan ke Facebook"
                            >
                                <IconifyIcon icon="lucide:facebook" width="16" class="sm:mr-1.5" />
                                <span class="hidden sm:inline">Facebook</span>
                            </a>
                            <a
                                :href="`https://twitter.com/intent/tweet?url=${encodeURIComponent(currentUrl)}&text=${encodeURIComponent(article.title)}`"
                                target="_blank"
                                class="flex items-center justify-center px-2 py-2 sm:px-3 sm:py-2.5 bg-sky-500 text-white rounded-lg hover:bg-sky-600 transition-colors text-xs sm:text-sm"
                                title="Bagikan ke Twitter"
                            >
                                <IconifyIcon icon="lucide:twitter" width="16" class="sm:mr-1.5" />
                                <span class="hidden sm:inline">Twitter</span>
                            </a>
                            <a
                                :href="`https://wa.me/?text=${encodeURIComponent(article.title + ' - ' + currentUrl)}`"
                                target="_blank"
                                class="flex items-center justify-center px-2 py-2 sm:px-3 sm:py-2.5 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-xs sm:text-sm"
                                title="Bagikan ke WhatsApp"
                            >
                                <IconifyIcon icon="simple-icons:whatsapp" width="16" class="sm:mr-1.5" />
                                <span class="hidden sm:inline">WhatsApp</span>
                            </a>
                            <button
                                @click="copyToClipboard"
                                class="flex items-center justify-center px-2 py-2 sm:px-3 sm:py-2.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-xs sm:text-sm"
                                title="Salin Link"
                            >
                                <IconifyIcon icon="lucide:copy" width="16" class="sm:mr-1.5" />
                                <span class="hidden sm:inline">Copy</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Related Articles -->
                <div v-if="relatedArticles.length > 0" class="mt-8">
                    <div class="rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 sm:p-8">
                        <h3 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mb-6">
                            Artikel Terkait
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                            <article 
                                v-for="relatedArticle in relatedArticles" 
                                :key="relatedArticle.id"
                                class="bg-gray-50 dark:bg-gray-800 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 group border border-gray-100 dark:border-gray-700"
                            >
                                <Link :href="relatedArticle.url" class="block overflow-hidden">
                                    <img
                                        :src="getImageUrl(relatedArticle.featured_image || relatedArticle.featured_image_url)"
                                        :alt="relatedArticle.title"
                                        class="w-full h-32 object-cover group-hover:scale-105 transition-transform duration-300"
                                        @error="onImageError($event, '/images/placeholder.png')"
                                    />
                                </Link>
                                <div class="p-4">
                                    <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">
                                        {{ relatedArticle.published_date }}
                                    </div>
                                    <Link :href="relatedArticle.url" class="block">
                                        <h4 class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors duration-300 line-clamp-2 text-sm sm:text-base">
                                            {{ relatedArticle.title }}
                                        </h4>
                                    </Link>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm mt-2 line-clamp-2">
                                        {{ relatedArticle.excerpt }}
                                    </p>
                                </div>
                            </article>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { onImageError } from '@/imageFallback'
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import IconifyIcon from '@/Components/IconifyIcon.vue'

const props = defineProps({
    article: Object,
    relatedArticles: Array,
    settings: Object,
})

const currentUrl = computed(() => {
    return window.location.href
})

const getImageUrl = (imagePath) => {
    if (!imagePath) return '/images/placeholder.png'
    
    if (imagePath.startsWith('http')) {
        return imagePath
    }
    
    if (imagePath.includes('storage/app/public/')) {
        return imagePath.replace(/.*storage\/app\/public\//, '/storage/')
    }
    
    if (imagePath.startsWith('articles/')) {
        return '/storage/' + imagePath
    }
    
    if (imagePath.startsWith('/')) {
        return imagePath
    }
    
    return '/storage/' + imagePath
}

const copyToClipboard = async () => {
    try {
        await navigator.clipboard.writeText(currentUrl.value)
        alert('Link berhasil disalin!')
    } catch (err) {
        console.error('Failed to copy: ', err)
    }
}

const capitalizeFirst = (str) => {
    if (!str) return ''
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase()
}
</script>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Custom prose styles for better readability */
.prose {
    color: #374151;
    line-height: 1.7;
}

/* Ensure dynamic HTML content inherits prose styles */
.prose-content :deep(p) {
    margin-bottom: 1.25rem;
    color: inherit;
    line-height: inherit;
}

.prose-content :deep(h1),
.prose-content :deep(h2),
.prose-content :deep(h3),
.prose-content :deep(h4),
.prose-content :deep(h5),
.prose-content :deep(h6) {
    color: #111827;
    font-weight: 600;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.prose-content :deep(h1) {
    font-size: 2rem;
    line-height: 1.2;
}

.prose-content :deep(h2) {
    font-size: 1.5rem;
    line-height: 1.3;
}

.prose-content :deep(h3) {
    font-size: 1.25rem;
    line-height: 1.4;
}

.prose-content :deep(strong) {
    font-weight: 600;
    color: #111827;
}

.prose-content :deep(em) {
    font-style: italic;
}

.prose-content :deep(a) {
    color: #2563eb;
    text-decoration: underline;
    font-weight: 500;
}

.prose-content :deep(a:hover) {
    color: #1d4ed8;
}

.prose-content :deep(ul),
.prose-content :deep(ol) {
    margin: 1.25rem 0;
    padding-left: 1.5rem;
}

.prose-content :deep(li) {
    margin: 0.25rem 0;
}

.prose-content :deep(blockquote) {
    border-left: 4px solid #e5e7eb;
    padding-left: 1rem;
    font-style: italic;
    color: #6b7280;
    margin: 1.5rem 0;
}

.prose-content :deep(code) {
    background-color: #f3f4f6;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-size: 0.875em;
    font-family: ui-monospace, SFMono-Regular, 'SF Mono', Consolas, 'Liberation Mono', Menlo, monospace;
}

.prose-content :deep(pre) {
    background-color: #1f2937;
    color: #f9fafb;
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin: 1.5rem 0;
}

.prose-content :deep(pre code) {
    background-color: transparent;
    padding: 0;
    color: inherit;
}

.prose-content :deep(img) {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
    margin: 1.5rem 0;
}

.prose-content :deep(table) {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5rem 0;
}

.prose-content :deep(th),
.prose-content :deep(td) {
    border: 1px solid #e5e7eb;
    padding: 0.75rem;
    text-align: left;
}

.prose-content :deep(th) {
    background-color: #f9fafb;
    font-weight: 600;
}

/* Dark mode styles */
.dark .prose {
    color: #d1d5db;
}

.dark .prose h1,
.dark .prose h2,
.dark .prose h3,
.dark .prose h4,
.dark .prose h5,
.dark .prose h6 {
    color: #f9fafb;
}

.dark .prose a {
    color: #60a5fa;
}

.dark .prose a:hover {
    color: #93c5fd;
}

.dark .prose blockquote {
    border-left-color: #4b5563;
    color: #9ca3af;
}

.dark .prose strong {
    color: #f9fafb;
}

.dark .prose code {
    background-color: #374151;
    color: #e5e7eb;
}

.dark .prose th {
    background-color: #374151;
    color: #f9fafb;
}

.dark .prose th,
.dark .prose td {
    border-color: #4b5563;
}

/* Dark mode for dynamic content */
.dark .prose-content :deep(p) {
    color: #d1d5db;
}

.dark .prose-content :deep(h1),
.dark .prose-content :deep(h2),
.dark .prose-content :deep(h3),
.dark .prose-content :deep(h4),
.dark .prose-content :deep(h5),
.dark .prose-content :deep(h6) {
    color: #f9fafb;
}

.dark .prose-content :deep(strong) {
    color: #f9fafb;
}

.dark .prose-content :deep(a) {
    color: #60a5fa;
}

.dark .prose-content :deep(a:hover) {
    color: #93c5fd;
}

.dark .prose-content :deep(blockquote) {
    border-left-color: #4b5563;
    color: #9ca3af;
}

.dark .prose-content :deep(code) {
    background-color: #374151;
    color: #e5e7eb;
}

.dark .prose-content :deep(th) {
    background-color: #374151;
    color: #f9fafb;
}

.dark .prose-content :deep(th),
.dark .prose-content :deep(td) {
    border-color: #4b5563;
}
</style>