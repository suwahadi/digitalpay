<template>
    <AppLayout :settings="settings">
        <Head title="Berita" />
        
        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 pt-24 pb-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header Section -->
                <div class="text-center mb-16">
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                        Berita & Update Terbaru
                    </h1>
                </div>

                <!-- Search and Filter Section -->
                <div class="mb-12">
                    <!-- Mobile: 2 columns, Desktop: 1 row -->
                    <div class="flex flex-col sm:grid sm:grid-cols-2 lg:flex lg:flex-row gap-4 max-w-2xl">
                        <!-- Search Input -->
                        <div class="relative flex-1">
                            <input
                                type="text"
                                v-model="searchQuery"
                                @input="handleSearch"
                                placeholder="Cari..."
                                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-800 dark:text-white bg-white text-gray-900 transition-all duration-200"
                            />
                            <IconifyIcon 
                                icon="lucide:search" 
                                width="18" 
                                class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500"
                            />
                        </div>
                        
                        <!-- Category Filter -->
                        <div class="relative lg:w-48">
                            <select
                                v-model="selectedCategory"
                                @change="handleCategoryFilter"
                                class="w-full px-4 py-2.5 pr-10 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent dark:bg-gray-800 dark:text-white bg-white text-gray-900 appearance-none transition-all duration-200"
                            >
                                <option value="">Semua Kategori</option>
                                <option v-for="category in categories" :key="category" :value="category">
                                    {{ category }}
                                </option>
                            </select>
                            <IconifyIcon 
                                icon="lucide:chevron-down" 
                                width="18" 
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-400 dark:text-gray-500 pointer-events-none"
                            />
                        </div>
                    </div>
                    
                    <!-- Filter Status -->
                    <div v-if="searchQuery || selectedCategory" class="mt-4 max-w-2xl">
                        <div class="flex flex-wrap gap-2 items-center">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Filter aktif:</span>
                            <span v-if="searchQuery" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                "{{ searchQuery }}"
                                <button @click="searchQuery = ''; handleSearch()" class="ml-2 hover:text-green-600">
                                    <IconifyIcon icon="lucide:x" width="14" />
                                </button>
                            </span>
                            <span v-if="selectedCategory" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                {{ selectedCategory }}
                                <button @click="selectedCategory = ''; handleCategoryFilter()" class="ml-2 hover:text-blue-600">
                                    <IconifyIcon icon="lucide:x" width="14" />
                                </button>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Articles Grid -->
                <div v-if="articles.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                    <article 
                        v-for="article in articles.data" 
                        :key="article.id"
                        class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-sm hover:shadow-lg transition-all duration-300 group"
                    >
                        <!-- Image as clickable link -->
                        <Link :href="article.url" class="block overflow-hidden">
                            <img
                                :src="getImageUrl(article.featured_image || article.featured_image_url)"
                                :alt="article.title"
                                class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300"
                                @error="onImageError($event, '/images/placeholder.png')"
                            />
                        </Link>
                        
                        <div class="p-6">
                            <div class="flex items-center justify-between text-sm text-gray-500 dark:text-gray-400 mb-3">
                                <div class="flex items-center">
                                    <IconifyIcon icon="lucide:calendar" width="16" class="mr-1" />
                                    {{ article.published_date }}
                                </div>
                            </div>
                            
                            <!-- Title as clickable link -->
                            <Link :href="article.url" class="block">
                                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3 group-hover:text-blue-600 transition-colors duration-300 line-clamp-2">
                                    {{ article.title }}
                                </h2>
                            </Link>
                            
                            <p class="text-gray-600 dark:text-gray-400 mb-4 line-clamp-3">
                                {{ article.excerpt }}
                            </p>
                            
                            <div class="flex items-center justify-between">
                                <Link
                                    :href="article.url"
                                    class="text-blue-600 hover:text-blue-700 font-semibold text-sm inline-flex items-center"
                                >
                                    Baca Selengkapnya
                                    <IconifyIcon icon="lucide:arrow-right" width="16" class="ml-1" />
                                </Link>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <IconifyIcon icon="lucide:newspaper" width="64" class="mx-auto text-gray-400 mb-4" />
                    <h3 class="text-xl font-semibold text-gray-500 dark:text-gray-400 mb-2">
                        Tidak ada data yang ditemukan
                    </h3>
                    <p class="text-gray-400 dark:text-gray-500">
                        Coba ubah kata kunci pencarian
                    </p>
                </div>

                <!-- Pagination -->
                <div v-if="articles.links && articles.links.length > 3" class="flex justify-center">
                    <nav class="flex items-center space-x-1">
                        <Link
                            v-for="link in articles.links"
                            :key="link.label"
                            :href="link.url"
                            :class="[
                                'px-3 py-2 text-sm rounded-md transition-colors duration-200',
                                link.active 
                                    ? 'bg-blue-600 text-white' 
                                    : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-gray-200 dark:hover:bg-gray-800',
                                !link.url ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                            v-html="link.label"
                            :disabled="!link.url"
                        />
                    </nav>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { onImageError } from '@/imageFallback'
import { ref, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { debounce } from 'lodash'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    articles: Object,
    filters: Object,
    categories: Array,
    settings: Object,
})

const searchQuery = ref(props.filters.search || '')
const selectedCategory = ref(props.filters.category || '')

const handleSearch = debounce(() => {
    router.get('/news', {
        search: searchQuery.value,
        category: selectedCategory.value,
    }, {
        preserveState: true,
        replace: true,
    })
}, 300)

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

const handleCategoryFilter = () => {
    router.get('/news', {
        search: searchQuery.value,
        category: selectedCategory.value,
    }, {
        preserveState: true,
        replace: true,
    })
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

.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>