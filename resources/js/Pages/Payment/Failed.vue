<template>
    <AppLayout>
        <Head :title="title" />

        <div class="min-h-screen bg-gray-50 dark:bg-gray-900 pt-20 pb-12">
            <div class="w-full mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 shadow-sm p-8 text-center">
                    <!-- Failed Icon -->
                    <div class="w-16 h-16 bg-red-100 dark:bg-red-900/20 rounded-full flex items-center justify-center mx-auto mb-6">
                        <IconifyIcon icon="lucide:x-circle" width="32" class="text-red-600 dark:text-red-400" />
                    </div>
                    
                    <!-- Failed Message -->
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                        Pembayaran Gagal
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mb-8">
                        Maaf {{ order.customer_name }}, pembayaran Anda tidak dapat diproses. Silakan coba lagi atau gunakan metode pembayaran lain.
                    </p>

                    <!-- Order Details -->
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-6 mb-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Nomor Pesanan:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ order.order_number }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Total:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ formatPrice(order.total_amount) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Tanggal:</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ formatDate(order.created_at) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600 dark:text-gray-400">Status:</span>
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400">
                                    <IconifyIcon icon="lucide:x" width="12" class="mr-1" />
                                    {{ getStatusText(order.payment_status) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <Link 
                            :href="`/payment/${order.order_number}`"
                            class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 transition-colors"
                        >
                            <IconifyIcon icon="lucide:refresh-cw" width="20" class="mr-2" />
                            Coba Lagi
                        </Link>
                        <Link 
                            href="/"
                            class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-lg text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors"
                        >
                            <IconifyIcon icon="lucide:home" width="20" class="mr-2" />
                            Kembali ke Beranda
                        </Link>
                    </div>

                    <!-- Contact Support -->
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Butuh bantuan? 
                            <a href="/faq" class="text-blue-600 dark:text-blue-400 hover:underline">
                                Lihat FAQ
                            </a> 
                            atau hubungi customer service kami.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import IconifyIcon from '@/Components/IconifyIcon.vue'

const props = defineProps({
    title: String,
    order: Object
})

const formatPrice = (price) => {
    const numericPrice = parseFloat(price || 0)
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(numericPrice)
}

const formatDate = (date) => {
    const d = new Date(date)
    const day = String(d.getDate()).padStart(2, '0')
    const month = d.toLocaleDateString('en-US', { month: 'short' })
    const year = d.getFullYear()
    const hours = String(d.getHours()).padStart(2, '0')
    const minutes = String(d.getMinutes()).padStart(2, '0')
    return `${day} ${month} ${year} ${hours}:${minutes}`
}

const getStatusText = (status) => {
    const texts = {
        'failed': 'Gagal',
        'cancelled': 'Dibatalkan',
        'expired': 'Kedaluwarsa'
    }
    return texts[status] || 'Gagal'
}
</script>