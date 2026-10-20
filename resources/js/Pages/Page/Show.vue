<template>
    <AppLayout :settings="settings">
        <Head :title="page.title" />

        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 pt-20 pb-12">
            <!-- Page Content Container -->
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
                            <li class="text-gray-900 dark:text-white truncate">
                                {{ page.title }}
                            </li>
                        </ol>
                    </nav>

                    <!-- Page Header -->
                    <div class="mb-8">
                        <h1 class="text-2xl sm:text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                            {{ page.title }}
                        </h1>
                    </div>

                    <!-- Loading Skeleton -->
                    <div v-if="isLoading" class="space-y-4">
                        <!-- Title Skeleton -->
                        <div class="w-3/4 h-8 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                        
                        <!-- Content Lines Skeleton -->
                        <div class="space-y-3 mt-8">
                            <div class="w-full h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                            <div class="w-11/12 h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                            <div class="w-4/5 h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                            <div class="w-full h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                            <div class="w-3/4 h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                        </div>
                        
                        <!-- Paragraph Break -->
                        <div class="h-6"></div>
                        
                        <!-- More Content Lines -->
                        <div class="space-y-3">
                            <div class="w-full h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                            <div class="w-5/6 h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                            <div class="w-full h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                            <div class="w-2/3 h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                        </div>
                        
                        <!-- Image Placeholder -->
                        <div class="w-full h-48 sm:h-64 bg-gray-200 dark:bg-gray-700 rounded animate-pulse mt-6"></div>
                        
                        <!-- Final Content Lines -->
                        <div class="space-y-3 mt-6">
                            <div class="w-4/5 h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                            <div class="w-full h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                            <div class="w-3/4 h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                        </div>
                    </div>
                    
                    <!-- Main Content Section -->
                    <div v-else>
                        <div 
                            class="prose dark:prose-invert max-w-none prose-blue prose-lg prose-content"
                            v-html="page.content"
                        ></div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    page: Object,
    title: String,
    settings: Object,
})

const isLoading = ref(true)

const formatDate = (dateString) => {
    const date = new Date(dateString)
    return date.toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

onMounted(() => {
    setTimeout(() => {
        isLoading.value = false
    }, 800)
})
</script>

<style scoped>
.prose {
    color: #374151;
    line-height: 1.7;
}

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

.prose-content :deep(ul) {
    list-style-type: disc;
}

.prose-content :deep(ol) {
    list-style-type: decimal;
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

.prose h1,
.prose h2,
.prose h3,
.prose h4,
.prose h5,
.prose h6 {
    color: #111827;
    font-weight: 600;
    margin-top: 2rem;
    margin-bottom: 1rem;
}

.prose h1 {
    font-size: 2rem;
    line-height: 1.2;
}

.prose h2 {
    font-size: 1.5rem;
    line-height: 1.3;
}

.prose h3 {
    font-size: 1.25rem;
    line-height: 1.4;
}

.prose p {
    margin-bottom: 1.25rem;
}

.prose a {
    color: #2563eb;
    text-decoration: underline;
    font-weight: 500;
}

.prose a:hover {
    color: #1d4ed8;
}

.prose blockquote {
    border-left: 4px solid #e5e7eb;
    padding-left: 1rem;
    font-style: italic;
    color: #6b7280;
    margin: 1.5rem 0;
}

.prose ul,
.prose ol {
    margin: 1.25rem 0;
    padding-left: 1.5rem;
}

.prose li {
    margin: 0.25rem 0;
}

.prose strong {
    font-weight: 600;
    color: #111827;
}

.prose em {
    font-style: italic;
}

.prose code {
    background-color: #f3f4f6;
    padding: 0.125rem 0.25rem;
    border-radius: 0.25rem;
    font-size: 0.875em;
    font-family: ui-monospace, SFMono-Regular, 'SF Mono', Consolas, 'Liberation Mono', Menlo, monospace;
}

.prose pre {
    background-color: #1f2937;
    color: #f9fafb;
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
    margin: 1.5rem 0;
}

.prose pre code {
    background-color: transparent;
    padding: 0;
    color: inherit;
}

.prose img {
    max-width: 100%;
    height: auto;
    border-radius: 0.5rem;
    margin: 1.5rem 0;
}

.prose table {
    width: 100%;
    border-collapse: collapse;
    margin: 1.5rem 0;
}

.prose th,
.prose td {
    border: 1px solid #e5e7eb;
    padding: 0.75rem;
    text-align: left;
}

.prose th {
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