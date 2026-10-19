<template>
    <section class="py-16 bg-white dark:bg-gray-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    Produk Populer
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400">
                    Produk terlaris dan paling diminati pelanggan
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-4">
                <ProductCard
                    v-for="product in products"
                    :key="product.id"
                    :product="product"
                />
            </div>

            <div class="text-center mt-8">
                <Link
                    href="/products"
                    class="btn btn-primary"
                >
                    Lihat Semua Produk
                </Link>
            </div>
        </div>
    </section>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import ProductCard from '../Products/ProductCard.vue'

defineProps({
    products: {
        type: Array,
        default: () => []
    }
})
</script>