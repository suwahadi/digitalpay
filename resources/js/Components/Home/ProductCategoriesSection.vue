<template>
    <section class="py-16 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    Kategori Produk
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400">
                    Temukan berbagai kategori produk digital yang tersedia
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-6">
                <div 
                    v-for="category in categories" 
                    :key="category.id"
                    class="group cursor-pointer"
                    @click="goToCategory(category)"
                >
                    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 text-center shadow-sm hover:shadow-md transition-all duration-300 group-hover:scale-105">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white">
                            <IconifyIcon :icon="category.icon" width="32" />
                        </div>
                        <h3 class="font-semibold text-gray-900 dark:text-white mb-1">
                            {{ category.name }}
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            {{ category.products_count }} produk
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { router } from '@inertiajs/vue3'

defineProps({
    categories: {
        type: Array,
        default: () => []
    }
})

const goToCategory = (category) => {
    router.visit(route('products.category', { category: category.slug }))
}
</script>