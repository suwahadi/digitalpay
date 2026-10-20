<template>
    <AppLayout>
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white mt-20">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="flex-shrink-0">
                            <Avatar 
                                :name="user.name" 
                                :image-url="user.avatar_url" 
                                size="xl" 
                                :border="true"
                                border-color="border-white/20"
                            />
                        </div>
                        <div class="min-w-0">
                            <h1 class="break-words text-2xl font-bold">Halo, {{ user.name }}</h1>
                            <p class="text-blue-100">Pantau pesanan dan kelola akun Anda di sini.</p>
                        </div>
                    </div>
                    <div class="mt-4 flex gap-3 sm:mt-0">
                        <Link
                            href="/user/history"
                            class="inline-flex min-h-[44px] items-center rounded-lg bg-white px-4 text-sm font-medium text-blue-700 transition-colors hover:bg-blue-50"
                        >
                            Riwayat Pesanan
                        </Link>
                        <Link
                            href="/user/profile"
                            class="inline-flex min-h-[44px] items-center rounded-lg border border-white/30 px-4 text-sm font-medium text-white transition-colors hover:bg-white/10"
                        >
                            Profil
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Navigation Tabs -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 mb-8">
                <div class="relative border-b border-gray-200 dark:border-gray-700">
                    <nav class="flex gap-6 overflow-x-auto px-4 sm:px-6" aria-label="Status pesanan">
                        <button
                            type="button"
                            @click="changeStatus('pending')"
                            :class="[
                                activeTab === 'pending'
                                    ? 'border-amber-600 text-amber-700 dark:text-amber-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center'
                            ]"
                        >
                            Menunggu
                            <span
                                :class="[
                                    activeTab === 'pending'
                                        ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/20 dark:text-amber-400'
                                        : 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-gray-300',
                                    'ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium'
                                ]"
                            >
                                {{ stats.pendingOrders }}
                            </span>
                        </button>

                        <button
                            type="button"
                            @click="changeStatus('paid')"
                            :class="[
                                activeTab === 'paid'
                                    ? 'border-green-600 text-green-700 dark:text-green-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center'
                            ]"
                        >
                            Lunas
                            <span
                                :class="[
                                    activeTab === 'paid'
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                                        : 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-gray-300',
                                    'ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium'
                                ]"
                            >
                                {{ stats.paidOrders }}
                            </span>
                        </button>

                        <button
                            type="button"
                            @click="changeStatus('cancelled')"
                            :class="[
                                activeTab === 'cancelled'
                                    ? 'border-red-500 text-red-600 dark:text-red-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center'
                            ]"
                        >
                            Dibatalkan
                            <span
                                :class="[
                                    activeTab === 'cancelled'
                                        ? 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-400'
                                        : 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-gray-300',
                                    'ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium'
                                ]"
                            >
                                {{ stats.cancelledOrders }}
                            </span>
                        </button>

                        <button
                            type="button"
                            @click="changeStatus('expired')"
                            :class="[
                                activeTab === 'expired'
                                    ? 'border-gray-500 text-gray-600 dark:text-gray-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center'
                            ]"
                        >
                            Kedaluwarsa
                            <span
                                :class="[
                                    activeTab === 'expired'
                                        ? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-400'
                                        : 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-gray-300',
                                    'ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium'
                                ]"
                            >
                                {{ stats.expiredOrders }}
                            </span>
                        </button>

                        <button
                            type="button"
                            @click="changeStatus('all')"
                            :class="[
                                activeTab === 'all'
                                    ? 'border-blue-500 text-blue-600 dark:text-blue-400'
                                    : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300',
                                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors flex items-center'
                            ]"
                        >
                            Semua
                            <span
                                :class="[
                                    activeTab === 'all'
                                        ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/20 dark:text-blue-400'
                                        : 'bg-gray-100 text-gray-900 dark:bg-gray-700 dark:text-gray-300',
                                    'ml-2 py-0.5 px-2.5 rounded-full text-xs font-medium'
                                ]"
                            >
                                {{ stats.totalOrders }}
                            </span>
                        </button>
                    </nav>
                    <div class="pointer-events-none absolute inset-y-0 right-0 w-10 bg-gradient-to-l from-white dark:from-gray-800 md:hidden" aria-hidden="true"></div>
                </div>

                <!-- Tab Content -->
                <div class="overflow-hidden">
                    <div v-if="recentOrders.length === 0" class="text-center py-16">
                        <div class="mx-auto h-24 w-24 text-gray-400 mb-4">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <p class="text-lg font-medium text-gray-900 dark:text-white mb-2">Tidak ada pesanan</p>
                        <p class="text-gray-600 dark:text-gray-400">Belum ada pesanan dengan status ini.</p>
                        <Link
                            href="/products"
                            class="mt-5 inline-flex min-h-[44px] items-center rounded-lg bg-blue-600 px-5 text-sm font-medium text-white transition-colors hover:bg-blue-700"
                        >
                            Lihat Produk
                        </Link>
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
                                    v-for="order in recentOrders"
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
                                            {{ order.first_product }}
                                            <span v-if="order.items_count > 1" class="text-gray-500 dark:text-gray-400">
                                                +{{ order.items_count - 1 }} lainnya
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
                                        {{ order.created_at }}
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
                            v-for="order in recentOrders"
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
                                {{ order.first_product }}
                                <span v-if="order.items_count > 1" class="text-gray-500 dark:text-gray-400">
                                    +{{ order.items_count - 1 }} lainnya
                                </span>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-lg font-medium text-gray-900 dark:text-white">
                                        Rp {{ formatCurrency(order.total_amount) }}
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ order.created_at }}
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

                    <!-- View All Orders Link -->
                    <div v-if="recentOrders.length > 0" class="px-6 pb-6">
                        <div class="text-center">
                            <Link
                                href="/user/history"
                                class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-lg text-blue-600 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 transition-colors"
                            >
                                Lihat Semua Riwayat
                                <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Avatar from '@/Components/Avatar.vue'

const props = defineProps({
    user: Object,
    stats: Object,
    recentOrders: Array,
    currentStatus: String,
})

const activeTab = ref(props.currentStatus || 'pending')

const changeStatus = (status) => {
    activeTab.value = status
    router.get('/user', { status }, {
        preserveState: true,
        preserveScroll: true,
    })
}

const formatCurrency = (amount) => {
    return new Intl.NumberFormat('id-ID').format(amount || 0)
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