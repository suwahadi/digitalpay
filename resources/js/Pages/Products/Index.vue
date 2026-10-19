<template>
    <AppLayout>
        <Head title="Produk" />

        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 pt-20 pb-12">
            <!-- Page Header -->
            <div class="bg-white dark:bg-gray-800 border-b dark:border-gray-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-center justify-between gap-3">
                            <div class="min-w-0">
                                <h1 class="text-2xl font-bold leading-tight text-gray-900 dark:text-white sm:text-3xl">
                                    Semua Produk
                                </h1>
                            </div>

                            <button
                                type="button"
                                @click="showFilters = !showFilters"
                                :aria-expanded="showFilters"
                                aria-controls="product-filters"
                                class="lg:hidden inline-flex shrink-0 items-center gap-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-200"
                            >
                                <IconifyIcon :icon="showFilters ? 'lucide:x' : 'lucide:sliders-horizontal'" width="18" />
                                <span>{{ showFilters ? 'Tutup' : 'Cari & Filter' }}</span>
                            </button>
                        </div>

                        <div
                            id="product-filters"
                            :class="showFilters ? 'grid' : 'hidden'"
                            class="grid-cols-1 gap-3 lg:flex lg:items-center"
                        >
                            <div class="relative lg:w-64">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <IconifyIcon icon="lucide:search" width="18" class="text-gray-400" />
                                </div>
                                <input
                                    v-model="searchQuery"
                                    @input="handleSearch"
                                    type="text"
                                    placeholder="Cari produk..."
                                    class="block w-full h-10 pl-10 pr-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>

                            <select
                                v-model="selectedCategory"
                                @change="handleCategoryFilter"
                                aria-label="Kategori"
                                class="block w-full h-10 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 lg:w-44"
                            >
                                <option value="">Semua Kategori</option>
                                <option
                                    v-for="category in categories"
                                    :key="category.id"
                                    :value="category.slug"
                                >
                                    {{ category.name }}
                                </option>
                            </select>

                            <select
                                v-model="sortBy"
                                @change="handleSort"
                                aria-label="Urutkan"
                                class="block w-full h-10 px-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 lg:w-44"
                            >
                                <option value="newest">Terbaru</option>
                                <option value="popular">Terpopuler</option>
                                <option value="price_low">Harga Terendah</option>
                                <option value="price_high">Harga Tertinggi</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <!-- Results Info -->
                <div class="flex items-center justify-between mb-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        Menampilkan {{ products.from }}-{{ products.to }} dari {{ products.total }} produk
                        <span v-if="filters.search" class="font-medium">
                            untuk "{{ filters.search }}"
                        </span>
                        <span v-if="filters.category" class="font-medium">
                            dalam kategori "{{ getCategoryName(filters.category) }}"
                        </span>
                    </p>
                    
                    <div class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                        <IconifyIcon icon="lucide:grid-3x3" width="16" />
                        <span>{{ products.data.length }} produk</span>
                    </div>
                </div>

                <!-- Products Grid - 6 Columns Desktop, 2 Columns Mobile -->
                <div v-if="products.data.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                    <ProductCard 
                        v-for="product in products.data" 
                        :key="product.id"
                        :product="product"
                        class="animate-fade-in-fast"
                    />
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-12">
                    <div class="mx-auto h-24 w-24 text-gray-400 dark:text-gray-500 mb-4">
                        <IconifyIcon icon="lucide:package-search" width="96" height="96" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        Tidak ada produk ditemukan
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-6">
                        Coba ubah filter pencarian atau jelajahi kategori lainnya.
                    </p>
                    <button
                        @click="clearFilters"
                        class="btn btn-primary"
                    >
                        Lihat Semua Produk
                    </button>
                </div>

                <!-- Pagination -->
                <div v-if="products.data.length > 0 && products.last_page > 1" class="mt-12">
                    <nav class="flex items-center justify-between border-t border-gray-200 dark:border-gray-700 px-4 py-3 sm:px-6">
                        <div class="flex flex-1 justify-between sm:hidden">
                            <Link
                                v-if="products.prev_page_url"
                                :href="products.prev_page_url"
                                class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Sebelumnya
                            </Link>
                            <Link
                                v-if="products.next_page_url"
                                :href="products.next_page_url"
                                class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-50 dark:hover:bg-gray-700"
                            >
                                Selanjutnya
                            </Link>
                        </div>
                        
                        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700 dark:text-gray-300">
                                    Menampilkan halaman
                                    <span class="font-medium">{{ products.current_page }}</span>
                                    dari
                                    <span class="font-medium">{{ products.last_page }}</span>
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <!-- Previous Page Link -->
                                    <Link
                                        v-if="products.prev_page_url"
                                        :href="products.prev_page_url"
                                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700"
                                    >
                                        <IconifyIcon icon="lucide:chevron-left" width="20" />
                                    </Link>
                                    
                                    <!-- Page Numbers -->
                                    <template v-for="(link, index) in products.links" :key="index">
                                        <Link
                                            v-if="link.url && !link.label.includes('Previous') && !link.label.includes('Next')"
                                            :href="link.url"
                                            :class="[
                                                'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                                                link.active
                                                    ? 'z-10 bg-blue-50 dark:bg-blue-900 border-blue-500 text-blue-600 dark:text-blue-400'
                                                    : 'bg-white dark:bg-gray-800 border-gray-300 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700'
                                            ]"
                                            v-html="link.label"
                                        />
                                        <span
                                            v-else-if="!link.url && !link.label.includes('Previous') && !link.label.includes('Next')"
                                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300"
                                            v-html="link.label"
                                        />
                                    </template>
                                    
                                    <!-- Next Page Link -->
                                    <Link
                                        v-if="products.next_page_url"
                                        :href="products.next_page_url"
                                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700"
                                    >
                                        <IconifyIcon icon="lucide:chevron-right" width="20" />
                                    </Link>
                                </nav>
                            </div>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import ProductCard from '@/Components/Products/ProductCard.vue'
import IconifyIcon from '@/Components/IconifyIcon.vue'

const props = defineProps({
    products: Object,
    title: String,
    categories: Array,
    filters: Object,
})

const searchQuery = ref(props.filters.search || '')
const selectedCategory = ref(props.filters.category || '')
const sortBy = ref(props.filters.sort || 'newest')
const showFilters = ref(false)

let searchTimeout = null
const handleSearch = () => {
    clearTimeout(searchTimeout)
    searchTimeout = setTimeout(() => {
        applyFilters()
    }, 500)
}

const handleCategoryFilter = () => {
    applyFilters()
}

const handleSort = () => {
    applyFilters()
}

const applyFilters = () => {
    const params = {}
    
    if (searchQuery.value) {
        params.search = searchQuery.value
    }
    
    if (selectedCategory.value) {
        params.category = selectedCategory.value
    }
    
    if (sortBy.value !== 'newest') {
        params.sort = sortBy.value
    }

    router.get('/products', params, {
        preserveState: true,
        preserveScroll: true,
    })
}

const clearFilters = () => {
    searchQuery.value = ''
    selectedCategory.value = ''
    sortBy.value = 'newest'
    
    router.get('/products', {}, {
        preserveState: false,
        preserveScroll: false,
    })
}

const getCategoryName = (slug) => {
    const category = props.categories.find(cat => cat.slug === slug)
    return category ? category.name : slug
}

watch(() => props.filters, (newFilters) => {
    searchQuery.value = newFilters.search || ''
    selectedCategory.value = newFilters.category || ''
    sortBy.value = newFilters.sort || 'newest'
}, { immediate: true })
</script>

<style scoped>
.animate-fade-in-fast {
    animation: fadeInFast 0.3s ease-in-out;
}

@keyframes fadeInFast {
    0% {
        opacity: 0;
        transform: translateY(10px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>