<template>
    <Link 
        :href="route('products.show', { slug: product.slug })" 
        class="flex group"
    >
        <div class="relative p-0 mt-16 shadow-none card dark:from-gray-800/80 dark:to-gray-800/30 dark:bg-gradient-to-t rounded-2xl">
            <!-- Product Image -->
            <div class="relative flex items-center justify-center transition-all duration-500 rounded-t-xl">
                <div class="w-full mx-3 -mt-16 bg-white shadow-lg animate-fade-in-fast shadow-gray-500/25 lg:mx-4 xl:mx-5 dark:from-gray-800/80 dark:to-bluegray dark:bg-gradient-to-t rounded-2xl">
                    <img 
                        loading="lazy"
                        decoding="async"
                        :src="product.image" 
                        :alt="product.name" 
                        class="aspect-video w-full object-cover rounded-2xl"
                        @error="handleImageError"
                    />
                </div>
            </div>

            <!-- Stock Status -->
            <div class="relative w-full text-xs transition-all duration-500 flex justify-center mt-2">
                <span 
                    :class="stockStatusClass"
                    class="px-3 py-1 text-white rounded-lg animate-slit-in-horizontal-fast"
                >
                    {{ stockStatusText }}
                </span>
            </div>

            <!-- Product Info -->
            <div class="relative px-3 pt-2 pb-5 rounded-b-xl">
                <!-- Category -->
                <div class="text-xs font-light text-center text-gray-600 md:text-sm dark:text-gray-300/80">
                    {{ product.category }}
                </div>

                <!-- Product Name -->
                <div class="text-sm font-medium leading-5 text-center capitalize md:text-base dark:text-gray-100">
                    {{ product.name }}
                </div>
                
                <!-- Price -->
                <div v-if="product.min_price" class="text-center mt-2">
                    <span class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                        Mulai {{ formatPrice(product.min_price) }}
                    </span>
                </div>
            </div>
        </div>
    </Link>
</template>

<script setup>
import { onImageError } from '@/imageFallback'
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'

const props = defineProps({
    product: Object,
})

const stockStatusClass = computed(() => {
    switch (props.product.stock_status) {
        case 'in_stock':
        case 'unlimited':
            return 'bg-blue-500'
        case 'low_stock':
            return 'bg-yellow-500'
        case 'out_of_stock':
            return 'bg-red-500'
        default:
            return 'bg-blue-500'
    }
})

const stockStatusText = computed(() => {
    switch (props.product.stock_status) {
        case 'in_stock':
        case 'unlimited':
            return 'Tersedia'
        case 'low_stock':
            return 'Tersisa Sedikit'
        case 'out_of_stock':
            return 'Habis'
        default:
            return 'Tersedia'
    }
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(price)
}

const handleImageError = (event) => {
    onImageError(event, '/images/placeholder.png')
}
</script>
