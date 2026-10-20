<template>
    <AppLayout>
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white mt-20">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold">Detail Pesanan</h1>
                        <p class="text-blue-200">{{ order.order_number }}</p>
                    </div>
                    <div class="mt-4 sm:mt-0 flex space-x-3">
                        <Link
                            href="/user/history"
                            class="inline-flex items-center px-4 py-2 border border-white/20 text-sm font-medium rounded-lg text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Kembali
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Order Status Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 mb-6">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Status</h2>
                        <span
                            :class="[
                                'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
                                getStatusBadgeColor(order.status)
                            ]"
                        >
                            {{ getStatusText(order.status) }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Tanggal Pesanan</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ formatDate(order.created_at) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Total Pembayaran</p>
                            <p class="font-medium text-gray-900 dark:text-white">Rp {{ formatCurrency(order.total_amount) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 dark:text-gray-400">Metode Pembayaran</p>
                            <p class="font-medium text-gray-900 dark:text-white">{{ order.payment_method?.name || 'Belum dipilih' }}</p>
                        </div>
                    </div>
                </div>
            </div>



            <!-- Order Items Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Item Pesanan</h2>
                    
                    <div class="space-y-4">
                        <div
                            v-for="item in order.items"
                            :key="item.id"
                            class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-xl"
                        >
                            <div class="flex-1">
                                <h3 class="font-medium text-gray-900 dark:text-white">{{ item.product?.name || 'Produk tidak ditemukan' }}</h3>
                                <p v-if="item.product_variant" class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ item.product_variant.name }}
                                </p>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ item.quantity }} x Rp {{ formatCurrency(item.price) }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="font-medium text-gray-900 dark:text-white">
                                    Rp {{ formatCurrency(item.quantity * item.price) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="mt-6 border-t border-gray-200 dark:border-gray-600 pt-4">
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                                <span class="text-gray-900 dark:text-white">Rp {{ formatCurrency(order.subtotal) }}</span>
                            </div>
                            <div v-if="order.fee_amount > 0" class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Fee Platform:</span>
                                <span class="text-gray-900 dark:text-white">Rp {{ formatCurrency(order.fee_amount) }}</span>
                            </div>
                            <div class="flex justify-between text-lg font-semibold border-t border-gray-200 dark:border-gray-600 pt-2">
                                <span class="text-gray-900 dark:text-white">Total:</span>
                                <span class="text-gray-900 dark:text-white">Rp {{ formatCurrency(order.total_amount) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div v-if="order.notes" class="mt-6 border-t border-gray-200 dark:border-gray-600 pt-4">
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white mb-2">Catatan:</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ order.notes }}</p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 border-t border-gray-200 dark:border-gray-600 pt-4 space-y-3">
                        <!-- Payment Button -->
                        <div v-if="order.status === 'pending'">
                            <Link
                                :href="`/payment/${order.order_number}`"
                                class="w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-xl text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                            >
                                Bayar Sekarang
                            </Link>
                        </div>
                        
                        <!-- Detail Order Button -->
                        <div>
                            <a
                                :href="`/payment/${order.order_number}`"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="w-full flex justify-center items-center py-3 px-4 border border-blue-600 text-sm font-medium rounded-xl text-blue-600 bg-white dark:bg-gray-800 hover:bg-blue-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                            >
                                Detail Order
                                <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
    order: Object,
})

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0)
}

const formatDate = (dateString) => {
    const d = new Date(dateString)
    const day = String(d.getDate()).padStart(2, '0')
    const month = d.toLocaleDateString('id-ID', { month: 'short' })
    const year = d.getFullYear()
    const hours = String(d.getHours()).padStart(2, '0')
    const minutes = String(d.getMinutes()).padStart(2, '0')
    return `${day} ${month} ${year} ${hours}:${minutes}`
}

const getStatusText = (status) => {
    const texts = {
        pending: 'Menunggu Pembayaran',
        paid: 'Sudah Dibayar',
        cancelled: 'Dibatalkan',
        expired: 'Kedaluwarsa',
    }
    return texts[status] || status
}

const getStatusBadgeColor = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
        paid: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        cancelled: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
        expired: 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
    }
    return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
}
</script>