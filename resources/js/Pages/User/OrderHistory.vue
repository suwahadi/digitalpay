<template>
    <AppLayout>
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white mt-20">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold">Riwayat Pesanan</h1>
                        <p class="text-blue-200">Kelola dan lihat semua transaksi Anda</p>
                    </div>
                    <div class="mt-4 sm:mt-0 flex space-x-3">
                        <Link
                            href="/user"
                            class="inline-flex items-center px-4 py-2 border border-white/20 text-sm font-medium rounded-lg text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Kembali
                        </Link>
                        <button
                            @click="logout"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-blue-600 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Status Tabs -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 mb-6">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="flex space-x-8 px-6 overflow-x-auto" aria-label="Tabs">
                        <button
                            v-for="(count, status) in statusCounts"
                            :key="status"
                            @click="changeStatus(status)"
                            :class="[
                                currentStatus === status
                                    ? getActiveTabColor(status)
                                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center'
                            ]"
                        >
                            {{ getStatusLabel(status) }}
                            <span
                                :class="[
                                    currentStatus === status
                                        ? getActiveCountColor(status)
                                        : 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-gray-300',
                                    'ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium'
                                ]"
                            >
                                {{ count }}
                            </span>
                        </button>
                    </nav>
                </div>

                <!-- Orders Table -->
                <div class="overflow-hidden">
                    <div v-if="orders.data.length === 0" class="text-center py-16">
                        <div class="mx-auto h-24 w-24 text-gray-400 mb-4">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">Tidak ada pesanan</p>
                        <p class="text-gray-500 dark:text-gray-400">Belum ada pesanan dengan status ini</p>
                    </div>

                    <!-- Desktop Table -->
                    <div v-else class="hidden md:block">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        ID Faktur
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Produk
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Total
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                        Tanggal
                                    </th>
                                    <th class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                <tr
                                    v-for="order in orders.data"
                                    :key="order.id"
                                    class="hover:bg-gray-50 dark:hover:bg-gray-700"
                                >
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ order.order_number }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 dark:text-white">
                                            {{ getFirstProduct(order) }}
                                            <span v-if="order.items.length > 1" class="text-gray-500 dark:text-gray-400">
                                                +{{ order.items.length - 1 }} lainnya
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                                            Rp {{ formatCurrency(order.total_amount) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                                getStatusBadgeColor(order.status)
                                            ]"
                                        >
                                            {{ getStatusText(order.status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                        {{ formatDate(order.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link
                                            :href="`/user/order/${order.order_number}`"
                                            class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300"
                                        >
                                            Detail
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Mobile Cards -->
                    <div class="md:hidden space-y-4 p-6">
                        <div
                            v-for="order in orders.data"
                            :key="order.id"
                            class="bg-gray-50 dark:bg-gray-700 rounded-xl p-4"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ order.order_number }}
                                </div>
                                <span
                                    :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                                        getStatusBadgeColor(order.status)
                                    ]"
                                >
                                    {{ getStatusText(order.status) }}
                                </span>
                            </div>
                            
                            <div class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                                {{ getFirstProduct(order) }}
                                <span v-if="order.items.length > 1" class="text-gray-500 dark:text-gray-400">
                                    +{{ order.items.length - 1 }} lainnya
                                </span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-lg font-medium text-gray-900 dark:text-white">
                                        Rp {{ formatCurrency(order.total_amount) }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ formatDate(order.created_at) }}
                                    </div>
                                </div>
                                <Link
                                    :href="`/user/order/${order.order_number}`"
                                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 dark:bg-blue-900/20 dark:text-blue-400 dark:hover:bg-blue-900/30 transition-colors"
                                >
                                    Detail
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="orders.links && orders.links.length > 3" class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            Menampilkan {{ orders.from }} - {{ orders.to }} dari {{ orders.total }} hasil
                        </div>
                        <div class="flex space-x-2">
                            <Link
                                v-for="link in orders.links"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'px-3 py-2 text-sm rounded-md transition-colors',
                                    link.active
                                        ? 'bg-blue-600 text-white'
                                        : link.url
                                        ? 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700'
                                        : 'text-gray-400 dark:text-gray-600 cursor-not-allowed'
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
    orders: Object,
    currentStatus: String,
    statusCounts: Object,
    filters: Object,
})

const logout = () => {
    router.post('/logout')
}

const changeStatus = (status) => {
    router.get('/user/history', { status }, {
        preserveState: true,
        preserveScroll: true,
    })
}

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

const getStatusLabel = (status) => {
    const labels = {
        all: 'Semua',
        pending: 'Menunggu',
        paid: 'Lunas',
        cancelled: 'Dibatalkan',
        expired: 'Kedaluwarsa',
    }
    return labels[status] || status
}

const getStatusText = (status) => {
    const texts = {
        pending: 'Menunggu',
        paid: 'Lunas',
        cancelled: 'Dibatalkan',
        expired: 'Kedaluwarsa',
    }
    return texts[status] || status
}

const getActiveTabColor = (status) => {
    const colors = {
        all: 'border-gray-500 text-gray-600 dark:text-gray-400',
        pending: 'border-yellow-500 text-yellow-600 dark:text-yellow-400',
        paid: 'border-green-500 text-green-600 dark:text-green-400',
        cancelled: 'border-red-500 text-red-600 dark:text-red-400',
        expired: 'border-gray-500 text-gray-600 dark:text-gray-400',
    }
    return colors[status] || 'border-gray-500 text-gray-600 dark:text-gray-400'
}

const getActiveCountColor = (status) => {
    const colors = {
        all: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
        pending: 'bg-yellow-100 text-yellow-600 dark:bg-yellow-900/20 dark:text-yellow-400',
        paid: 'bg-green-100 text-green-600 dark:bg-green-900/20 dark:text-green-400',
        cancelled: 'bg-red-100 text-red-600 dark:bg-red-900/20 dark:text-red-400',
        expired: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400',
    }
    return colors[status] || 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'
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

const getFirstProduct = (order) => {
    return order.items && order.items.length > 0 ? order.items[0].product?.name || 'Produk tidak ditemukan' : 'Tidak ada produk'
}
</script>