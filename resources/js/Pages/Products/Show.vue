<template>
    <AppLayout>
        <Head :title="product.name" />

        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 pt-20 pb-12">
            <!-- Breadcrumb -->
            <div class="bg-white dark:bg-gray-800 border-b dark:border-gray-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="flex items-center space-x-2 text-sm">
                            <li>
                                <Link href="/" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                    Beranda
                                </Link>
                            </li>
                            <IconifyIcon icon="lucide:chevron-right" width="16" class="text-gray-400" />
                            <li>
                                <Link href="/products" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                                    Produk
                                </Link>
                            </li>
                            <IconifyIcon icon="lucide:chevron-right" width="16" class="text-gray-400" />
                            <li class="text-gray-900 dark:text-white font-medium">
                                {{ product.name }}
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>

            <!-- Product Header -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
                    <!-- Product Image -->
                    <div class="flex justify-center">
                        <div class="w-full max-w-md">
                            <div class="aspect-square bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 p-6 shadow-sm">
                                <img 
                                    :src="product.image" 
                                    :alt="product.name"
                                    class="w-full h-full object-contain"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Product Info -->
                    <div class="space-y-6">
                        <!-- Category Badge -->
                        <div class="flex items-center space-x-2">
                            <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs font-medium rounded-full">
                                {{ product.category.name }}
                            </span>
                            <div class="flex items-center space-x-1 text-sm text-gray-500 dark:text-gray-400">
                                <IconifyIcon icon="lucide:eye" width="16" />
                                <span>{{ formatNumber(viewCount) }} Dilihat</span>
                            </div>
                            <div class="flex items-center space-x-1 text-sm text-gray-500 dark:text-gray-400">
                                <IconifyIcon icon="lucide:shopping-bag" width="16" />
                                <span>{{ formatNumber(salesCount) }} Terjual</span>
                            </div>
                        </div>

                        <!-- Product Title -->
                        <div>
                            <h1 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                {{ product.name }}
                            </h1>
                            <p class="text-gray-600 dark:text-gray-400" v-if="product.short_description">
                                {{ product.short_description }}
                            </p>
                        </div>

                        <!-- Sharing Buttons -->
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 sm:mb-3">
                                Bagikan:
                            </h3>
                            <div class="grid grid-cols-6 gap-2">
                                <button 
                                    v-for="social in socialButtons" 
                                    :key="social.name"
                                    @click="shareProduct(social.platform)"
                                    class="flex items-center justify-center px-1.5 py-2 sm:px-2 sm:py-1.5 rounded-lg transition-colors text-xs sm:text-xs"
                                    :class="getSocialButtonClass(social.platform)"
                                    :title="`Bagikan ke ${social.name}`"
                                >
                                    <!-- Mobile Icon -->
                                    <IconifyIcon :icon="social.icon" width="14" class="sm:hidden" />
                                    <!-- Desktop Icon -->
                                    <IconifyIcon :icon="social.icon" width="12" class="hidden sm:block sm:mr-1" />
                                    <span class="hidden sm:inline text-[10px]">{{ social.name }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Login Status Notice -->
                        <div v-if="!isLoggedIn" class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                            <div class="flex items-start space-x-3">
                                <IconifyIcon icon="lucide:user-circle" width="20" class="text-blue-600 dark:text-blue-400 mt-0.5" />
                                <div class="text-sm">
                                    <p class="text-blue-800 dark:text-blue-200 font-medium">
                                        Login member dan nikmati kemudahan berbelanja.
                                    </p>
                                    <p class="text-blue-700 dark:text-blue-300 mt-1">
                                        <Link href="/login" class="font-semibold text-blue-600 dark:text-blue-400 hover:underline">
                                            Klik di sini untuk login</Link>.</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Logged In Notice -->
                        <div v-else class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                            <div class="flex items-start space-x-3">
                                <IconifyIcon icon="lucide:user-check" width="20" class="text-green-600 dark:text-green-400 mt-0.5" />
                                <div class="text-sm">
                                    <p class="text-green-800 dark:text-green-200 font-medium">
                                        Halo, {{ $page.props.auth?.user?.name || 'User' }}
                                    </p>
                                    <p class="text-green-700 dark:text-green-300 mt-1">
                                        Silahkan pilih varian produk untuk melanjutkan pemesanan.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Variants -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 shadow-sm overflow-hidden">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">
                            {{ product.name }}
                        </h2>

                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div 
                                v-for="variant in sortedVariants" 
                                :key="variant.id"
                                class="border dark:border-gray-600 rounded-lg p-4 hover:border-blue-300 dark:hover:border-blue-600 transition-colors cursor-pointer"
                                :class="{ 
                                    'border-blue-500 dark:border-blue-400 bg-blue-50 dark:bg-blue-900/20': selectedVariant?.id === variant.id,
                                    'opacity-50 cursor-not-allowed': !variant.is_active || variant.stock_status === 'Habis'
                                }"
                                @click="selectVariant(variant)"
                            >
                                <div class="space-y-3">
                                    <div>
                                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm">
                                            {{ variant.name }}
                                        </h3>
                                        <div class="flex flex-wrap items-center gap-2 mt-2">
                                            <span 
                                                class="text-xs px-2 py-1 rounded"
                                                :class="getStockStatusClass(variant.stock_status)"
                                            >
                                                {{ variant.stock_status }}
                                            </span>
                                            <div 
                                                v-if="variant.auto_delivery === true" 
                                                class="flex items-center space-x-1 text-green-600 dark:text-green-400"
                                            >
                                                <IconifyIcon icon="lucide:zap" width="14" />
                                                <span class="text-xs font-medium">Instan</span>
                                            </div>
                                            <div 
                                                v-else 
                                                class="flex items-center space-x-1 text-yellow-600 dark:text-yellow-400"
                                            >
                                                <IconifyIcon icon="lucide:user-check" width="14" />
                                                <span class="text-xs font-medium">Manual</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="flex items-center space-x-2">
                                            <span class="text-lg font-bold text-gray-900 dark:text-white">
                                                {{ formatPrice(variant.price) }}
                                            </span>
                                            <span 
                                                v-if="variant.original_price && variant.original_price > variant.price" 
                                                class="text-sm text-gray-500 line-through"
                                            >
                                                {{ formatPrice(variant.original_price) }}
                                            </span>
                                        </div>
                                        <div 
                                            v-if="variant.discount_percentage" 
                                            class="text-xs text-red-600 dark:text-red-400 font-medium"
                                        >
                                            Disc {{ variant.discount_percentage }}%
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Selected Variant Actions -->
                        <div v-if="selectedVariant" class="mt-6 pt-6 border-t dark:border-gray-600">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">
                                    {{ product.name }} - {{ selectedVariant.name }}
                                </h4>

                                <div class="text-gray-600 dark:text-gray-400 mb-2 mt-1" v-html="selectedVariant.description"></div>
                                
                                <div class="space-y-4">
                                    <!-- Price Section -->
                                    <div class="text-center sm:text-left">
                                        <span class="text-sm text-gray-600 dark:text-gray-400">Harga:</span>
                                        <div class="flex items-center justify-center sm:justify-start space-x-2 mt-1">
                                            <span class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                                {{ formatPrice(selectedVariant.price) }}
                                            </span>
                                            <span 
                                                v-if="selectedVariant.original_price && selectedVariant.original_price > selectedVariant.price" 
                                                class="text-lg text-gray-500 line-through"
                                            >
                                                {{ formatPrice(selectedVariant.original_price) }}
                                            </span>
                                        </div>
                                        <div 
                                            v-if="selectedVariant.discount_percentage" 
                                            class="text-sm text-red-600 dark:text-red-400 font-medium mt-1"
                                        >
                                            Hemat {{ selectedVariant.discount_percentage }}%!
                                        </div>
                                    </div>
                                    
                                    <!-- Buy Button -->
                                    <div class="w-full">
                                        <button 
                                            @click="showOrderConfirmation"
                                            :disabled="!selectedVariant.is_active || selectedVariant.stock_status === 'Habis'"
                                            class="w-full btn btn-primary py-4 text-lg font-semibold disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2"
                                        >
                                            <IconifyIcon icon="lucide:shopping-cart" width="20" />
                                            <span>{{ selectedVariant.stock_status === 'Habis' ? 'Stok Habis' : 'Beli Sekarang' }}</span>
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Description -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 shadow-sm p-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Deskripsi Produk</h3>
                    
                    <div class="prose dark:prose-invert max-w-none">
                        <div class="text-gray-600 dark:text-gray-400" v-if="product.description" v-html="product.description"></div>
                        <div v-else class="text-gray-500 dark:text-gray-400 italic">
                            Tidak ada deskripsi tersedia untuk produk ini.
                        </div>
                    </div>
                    
                    <!-- Product Features -->
                    <div v-if="product.features && product.features.length > 0" class="mt-6">
                        <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Fitur Unggulan:</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div 
                                v-for="(feature, index) in product.features" 
                                :key="index"
                                class="flex items-start space-x-2"
                            >
                                <IconifyIcon icon="lucide:check-circle" width="18" class="text-green-500 mt-0.5 flex-shrink-0" />
                                <span class="text-gray-700 dark:text-gray-300">{{ feature }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 shadow-sm p-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Kamu Punya Pertanyaan?</h3>
                    
                    <div class="space-y-4">
                        <div 
                            v-for="faq in productFaqs" 
                            :key="faq.id"
                            class="border dark:border-gray-600 rounded-lg overflow-hidden"
                        >
                            <button
                                @click="toggleFaq(faq.id)"
                                class="w-full px-4 py-3 text-left bg-gray-50 dark:bg-gray-700 hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors focus:outline-none"
                            >
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900 dark:text-white">{{ faq.question }}</span>
                                    <IconifyIcon 
                                        icon="lucide:chevron-down" 
                                        width="18"
                                        :class="{ 'rotate-180': openFaqs.includes(faq.id) }"
                                        class="transition-transform duration-200 text-gray-500"
                                    />
                                </div>
                            </button>
                            <Transition
                                name="faq-slide"
                                enter-active-class="transition-all duration-300 ease-out"
                                leave-active-class="transition-all duration-200 ease-in"
                                enter-from-class="opacity-0 max-h-0"
                                enter-to-class="opacity-100 max-h-96"
                                leave-from-class="opacity-100 max-h-96"
                                leave-to-class="opacity-0 max-h-0"
                            >
                                <div v-if="openFaqs.includes(faq.id)" class="overflow-hidden">
                                    <div class="px-4 py-3 bg-white dark:bg-gray-800 border-t dark:border-gray-600">
                                        <p class="text-gray-600 dark:text-gray-400 leading-relaxed">{{ faq.answer }}</p>
                                    </div>
                                </div>
                            </Transition>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Products -->
            <div v-if="relatedProducts.length > 0" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 shadow-sm p-6">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Produk Terkait</h3>
                    
                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6 gap-6">
                        <ProductCard 
                            v-for="relatedProduct in relatedProducts" 
                            :key="relatedProduct.id"
                            :product="relatedProduct"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Notification Toast -->
        <Transition
            name="toast"
            enter-active-class="transition-all duration-300 ease-out"
            leave-active-class="transition-all duration-200 ease-in"
            enter-from-class="opacity-0 translate-y-2 scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-from-class="opacity-100 translate-y-0 scale-100"
            leave-to-class="opacity-0 translate-y-2 scale-95"
        >
            <div 
                v-if="showNotification"
                class="fixed top-24 right-4 z-50 flex items-center space-x-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg shadow-lg px-4 py-3 min-w-[280px]"
            >
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                        <IconifyIcon icon="lucide:check" width="18" class="text-green-600 dark:text-green-400" />
                    </div>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                        Link berhasil disalin!
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Siap untuk dibagikan
                    </p>
                </div>
                <button 
                    @click="showNotification = false"
                    class="flex-shrink-0 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                >
                    <IconifyIcon icon="lucide:x" width="16" />
                </button>
            </div>
        </Transition>

        <!-- Order Confirmation Modal -->
        <Transition
            name="modal"
            enter-active-class="transition-all duration-300 ease-out"
            leave-active-class="transition-all duration-200 ease-in"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div 
                v-if="showConfirmationModal" 
                class="fixed inset-0 z-50 flex items-center justify-center px-4"
                @click="closeConfirmationModal"
            >
                <Transition
                    name="modal-backdrop"
                    enter-active-class="transition-opacity duration-300 ease-out"
                    leave-active-class="transition-opacity duration-200 ease-in"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <div class="absolute inset-0 bg-black bg-opacity-50 backdrop-blur-sm"></div>
                </Transition>
                <Transition
                    name="modal-content"
                    enter-active-class="transition-all duration-300 ease-out"
                    leave-active-class="transition-all duration-200 ease-in"
                    enter-from-class="opacity-0 scale-95 translate-y-4"
                    enter-to-class="opacity-100 scale-100 translate-y-0"
                    leave-from-class="opacity-100 scale-100 translate-y-0"
                    leave-to-class="opacity-0 scale-95 translate-y-4"
                >
                    <div 
                        v-if="showConfirmationModal"
                        class="relative bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full max-h-[90vh] overflow-y-auto shadow-2xl ring-1 ring-gray-200 dark:ring-gray-700 ring-opacity-20"
                        @click.stop
                    >
                        <!-- Modal Header -->
                        <div class="flex items-center justify-between p-6 border-b dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                Konfirmasi Pembelian
                            </h3>
                            <button 
                                @click="closeConfirmationModal"
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-all duration-200 hover:scale-110 hover:rotate-90"
                            >
                                <IconifyIcon icon="lucide:x" width="24" />
                            </button>
                        </div>

                        <!-- Modal Body -->
                        <div class="p-6 space-y-6">
                            <!-- Product Summary -->
                            <div class="flex space-x-4">
                                <img 
                                    :src="product.image" 
                                    :alt="product.name"
                                    class="w-16 h-16 object-contain bg-gray-100 dark:bg-gray-700 rounded-lg"
                                />
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900 dark:text-white">
                                        {{ product.name }}
                                    </h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        {{ product.category.name }}
                                    </p>
                                    <div class="mt-1">
                                        <span class="text-xs px-2 py-1 rounded" :class="getStockStatusClass(selectedVariant.stock_status)">
                                            {{ selectedVariant.stock_status }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Variant Details -->
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <h5 class="font-medium text-gray-900 dark:text-white mb-3">Detail Varian:</h5>
                                <div class="text-sm text-gray-600 dark:text-gray-400 prose prose-sm dark:prose-invert max-w-none" v-html="selectedVariant.description"></div>
                            </div>

                            <!-- Delivery Notice -->
                            <div 
                                v-if="selectedVariant.auto_delivery === true" 
                                class="flex items-center space-x-2 text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 p-3 rounded-lg"
                            >
                                <IconifyIcon icon="lucide:zap" width="18" />
                                <span class="text-sm font-medium">Pengiriman Instan - Produk akan dikirim otomatis setelah pembayaran dikonfirmasi</span>
                            </div>
                            <div 
                                v-else 
                                class="flex items-center space-x-2 text-yellow-600 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/20 p-3 rounded-lg"
                            >
                                <IconifyIcon icon="lucide:user-check" width="18" />
                                <span class="text-sm font-medium">Pengiriman Manual - Produk akan diproses dan dikirim secara manual oleh Admin</span>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex space-x-3 p-6 border-t dark:border-gray-700">
                            <button 
                                @click="closeConfirmationModal"
                                class="flex-1 px-4 py-3 text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200 dark:hover:bg-gray-600 transition-all duration-200 font-medium hover:scale-105 active:scale-95"
                            >
                                Batal
                            </button>
                            <button 
                                @click="proceedToCheckout"
                                class="flex-1 px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-200 font-medium flex items-center justify-center space-x-2 hover:scale-105 active:scale-95 hover:shadow-lg"
                            >
                                <span>Lanjut</span>
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </AppLayout>
</template>

<script setup>
import { ref, computed, inject } from 'vue'
import { Head, Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import ProductCard from '@/Components/Products/ProductCard.vue'
import IconifyIcon from '@/Components/IconifyIcon.vue'

const route = inject('route')
const $page = usePage()

const props = defineProps({
    product: Object,
    title: String,
    relatedProducts: Array,
})

// Reactive data
const selectedVariant = ref(null)
const openFaqs = ref([])
const showConfirmationModal = ref(false)
const showNotification = ref(false)

// Computed properties
const sortedVariants = computed(() => {
    if (!props.product.variants) return []
    return [...props.product.variants].sort((a, b) => {
        // Sort by sort_order first, then by id as fallback
        const sortOrderA = a.sort_order || 0
        const sortOrderB = b.sort_order || 0
        if (sortOrderA !== sortOrderB) {
            return sortOrderA - sortOrderB
        }
        return a.id - b.id
    })
})

// Get view and sales count from product data
const viewCount = computed(() => props.product.view_count || 0)
const salesCount = computed(() => props.product.sales_count || 0)

// Check if user is logged in from page props
const isLoggedIn = computed(() => {
    return !!$page.props.auth?.user
})

// Social sharing buttons
const socialButtons = [
    { name: 'Facebook', platform: 'facebook', icon: 'simple-icons:facebook', color: 'text-blue-600' },
    { name: 'Twitter', platform: 'twitter', icon: 'simple-icons:x', color: 'text-gray-900 dark:text-white' },
    { name: 'Telegram', platform: 'telegram', icon: 'simple-icons:telegram', color: 'text-blue-500' },
    { name: 'WhatsApp', platform: 'whatsapp', icon: 'simple-icons:whatsapp', color: 'text-green-500' },
    { name: 'Email', platform: 'email', icon: 'lucide:mail', color: 'text-gray-600' },
    { name: 'Copy', platform: 'copy', icon: 'lucide:copy', color: 'text-gray-600' },
]

// Product-specific FAQs (this would come from backend)
const productFaqs = ref([
    {
        id: 1,
        question: `Bagaimana cara membeli ${props.product.name}?`,
        answer: `Kamu bisa membeli ${props.product.name} dengan cara memilih variant, kemudian membaca deskripsi jika sudah maka tambahkan ke dalam keranjang dan pilih metode pembayarannya ya!`
    },
    {
        id: 2,
        question: 'Apakah ini aman dan bergaransi?',
        answer: 'Yes, kamu akan mendapatkan garansi sesuai durasi yang kamu beli'
    },
    {
        id: 3,
        question: 'Apa yang harus saya lakukan jika terjadi masalah?',
        answer: 'Kamu bisa menghubungi admin dengan cara menekan icon hubungi kami yang berada di pojok kanan ya!'
    },
    {
        id: 4,
        question: 'Perbedaan 1 Profile 2 User dengan 1 Profile 1 User?',
        answer: '1 Profile 2 User adalah jenis profil yang dapat digunakan oleh dua orang, yaitu kamu dan satu pengguna lainnya. Namun, dengan varian ini, terdapat kemungkinan terjadinya batasan layar (screen limit) ketika digunakan secara bersamaan. Sebaliknya, 1 Profile 1 User adalah jenis profil yang dirancang khusus hanya untuk kamu. Dengan profil ini, kamu tidak akan mengalami gangguan atau batasan layar saat menikmati tontonan favoritmu!'
    },
])

// Methods
const selectVariant = (variant) => {
    if (variant.is_active && variant.stock_status !== 'Habis') {
        selectedVariant.value = variant
    }
}

const showOrderConfirmation = () => {
    if (selectedVariant.value) {
        showConfirmationModal.value = true
    }
}

const closeConfirmationModal = () => {
    showConfirmationModal.value = false
}

const proceedToCheckout = () => {
    if (selectedVariant.value) {
        // Redirect to checkout with selected product data
        router.post('/checkout', {
            product_id: props.product.id,
            variant_id: selectedVariant.value.id,
            quantity: 1
        })
    }
}

const toggleFaq = (faqId) => {
    const index = openFaqs.value.indexOf(faqId)
    if (index > -1) {
        openFaqs.value.splice(index, 1)
    } else {
        openFaqs.value.push(faqId)
    }
}

const shareProduct = (platform) => {
    const url = window.location.href
    const siteName = $page.props.settings?.title || 'DigitalPay'
    const title = `${props.product.name} - ${siteName}`
    
    switch (platform) {
        case 'facebook':
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`)
            break
        case 'twitter':
            window.open(`https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`)
            break
        case 'telegram':
            window.open(`https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent(title)}`)
            break
        case 'whatsapp':
            window.open(`https://wa.me/?text=${encodeURIComponent(`${title} ${url}`)}`)
            break
        case 'email':
            window.open(`mailto:?subject=${encodeURIComponent(title)}&body=${encodeURIComponent(url)}`)
            break
        case 'copy':
            navigator.clipboard.writeText(url).then(() => {
                showNotification.value = true
                setTimeout(() => {
                    showNotification.value = false
                }, 3000)
            }).catch(() => {
                alert('Gagal menyalin link')
            })
            break
    }
}

const getSocialButtonClass = (platform) => {
    switch (platform) {
        case 'facebook':
            return 'bg-blue-600 text-white hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700'
        case 'twitter':
            return 'bg-sky-500 text-white hover:bg-sky-600 dark:bg-sky-500 dark:hover:bg-sky-600'
        case 'telegram':
            return 'bg-blue-500 text-white hover:bg-blue-600 dark:bg-blue-500 dark:hover:bg-blue-600'
        case 'whatsapp':
            return 'bg-green-500 text-white hover:bg-green-600 dark:bg-green-500 dark:hover:bg-green-600'
        case 'email':
            return 'bg-gray-600 text-white hover:bg-gray-700 dark:bg-gray-600 dark:hover:bg-gray-700'
        case 'copy':
            return 'bg-gray-600 text-white hover:bg-gray-700 dark:bg-gray-600 dark:hover:bg-gray-700'
        default:
            return 'bg-gray-600 text-white hover:bg-gray-700 dark:bg-gray-600 dark:hover:bg-gray-700'
    }
}

const formatPrice = (price) => {
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(price)
}

const formatNumber = (number) => {
    if (number >= 1000) {
        return (number / 1000).toFixed(1) + 'Rb'
    }
    return number.toString()
}

const getStockStatusClass = (status) => {
    if (status === 'Habis') {
        return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200'
    } else if (status.includes('Tersisa')) {
        return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200'
    } else {
        return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
    }
}

// Set first available variant as default
if (props.product.variants && props.product.variants.length > 0) {
    const sortedAvailableVariants = sortedVariants.value.filter(v => v.is_active && v.stock_status !== 'Habis')
    if (sortedAvailableVariants.length > 0) {
        selectedVariant.value = sortedAvailableVariants[0]
    }
}
</script>

<style scoped>
.faq-slide-enter-active,
.faq-slide-leave-active {
    transition: all 0.3s ease;
}

.faq-slide-enter-from,
.faq-slide-leave-to {
    opacity: 0;
    max-height: 0;
}

.faq-slide-enter-to,
.faq-slide-leave-from {
    opacity: 1;
    max-height: 24rem;
}

/* Modal transitions */
.modal-enter-active,
.modal-leave-active {
    transition: all 0.3s ease-out;
}

.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}

.modal-enter-to,
.modal-leave-from {
    opacity: 1;
}

/* Modal backdrop transitions */
.modal-backdrop-enter-active,
.modal-backdrop-leave-active {
    transition: opacity 0.3s ease-out;
}

.modal-backdrop-enter-from,
.modal-backdrop-leave-to {
    opacity: 0;
}

.modal-backdrop-enter-to,
.modal-backdrop-leave-from {
    opacity: 1;
}

/* Modal content transitions */
.modal-content-enter-active,
.modal-content-leave-active {
    transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-content-enter-from,
.modal-content-leave-to {
    opacity: 0;
    transform: scale(0.92) translateY(2rem);
}

.modal-content-enter-to,
.modal-content-leave-from {
    opacity: 1;
    transform: scale(1) translateY(0);
}

/* Add smooth backdrop blur transition */
.backdrop-blur-sm {
    backdrop-filter: blur(4px);
    -webkit-backdrop-filter: blur(4px);
}

/* Additional smooth button transitions */
.transition-colors {
    transition-property: color, background-color, border-color, text-decoration-color, fill, stroke;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 200ms;
}
</style>