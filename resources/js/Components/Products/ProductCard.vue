<template>
    <Link
        :href="route('products.show', { slug: product.slug })"
        class="group flex"
    >
        <div
            class="card flex w-full flex-col overflow-hidden rounded-2xl transition-shadow duration-200 group-hover:shadow-md"
            :class="{ 'opacity-75': isOutOfStock }"
        >
            <img
                loading="lazy"
                decoding="async"
                :src="product.image"
                :alt="product.name"
                width="640"
                height="360"
                class="aspect-video w-full object-cover"
                :class="{ grayscale: isOutOfStock }"
                @error="handleImageError"
            />

            <div class="flex flex-1 flex-col items-center px-3 pt-3 pb-5 text-center">
                <span :class="stockStatusClass" class="rounded-md px-2.5 py-0.5 text-xs font-medium text-white">
                    {{ stockStatusText }}
                </span>
                <div class="mt-2 text-xs text-gray-600 md:text-sm dark:text-gray-300">
                    {{ product.category }}
                </div>
                <h3 class="text-sm font-medium leading-5 text-gray-900 md:text-base dark:text-gray-100">
                    {{ product.name }}
                </h3>
                <div v-if="product.min_price" class="mt-2 text-sm font-semibold text-blue-600 dark:text-blue-400">
                    Mulai {{ formatPrice(product.min_price) }}
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

const isOutOfStock = computed(() => props.product.stock_status === 'out_of_stock')

const stockStatusClass = computed(() => {
    switch (props.product.stock_status) {
        case 'low_stock':
            return 'bg-amber-700'
        case 'out_of_stock':
            return 'bg-red-600'
        default:
            return 'bg-blue-600'
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
