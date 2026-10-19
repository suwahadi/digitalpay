﻿<template>
    <AppLayout v-if="order && order.order_number">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Order #{{ order.order_number }}
            </h2>
        </template>

        <div class="py-12">
            <div class="w-full mx-auto max-w-4xl py-12 px-2 sm:px-4">
                <!-- Dynamic Title -->
                <Head>
                    <title>Order ID: #{{ order.order_number }}</title>
                    <meta name="description" :content="`Invoice untuk order ${order.order_number} di ${$page.props.settings?.title || 'DigitalPay'}`">
                </Head>

                <div class="min-h-screen bg-gray-50 dark:bg-gray-900 transition-colors">
                    <!-- Progress Bar (if still loading) -->
                    <div v-if="reloadProgress < 100" class="fixed top-0 left-0 w-full z-50">
                        <div class="bg-blue-600 h-1 transition-all duration-300" 
                             :style="{ width: reloadProgress + '%' }">
                        </div>
                        <div class="bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700 p-4 text-center">
                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                Mengambil informasi terbaru... {{ Math.round(reloadProgress) }}%
                            </div>
                        </div>
                    </div>

                    <!-- Main Content Container -->
                    <div class="relative overflow-hidden rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4 sm:p-8" 
                         :class="{ 'mt-20': reloadProgress < 100 }">
                        <div v-if="isLunas" class="pointer-events-none absolute right-0 top-0 h-28 w-28 overflow-hidden sm:h-32 sm:w-32" aria-hidden="true">
                            <div class="absolute right-[-44px] top-[26px] w-[170px] rotate-45 bg-green-600 py-1.5 text-center text-sm font-bold tracking-[0.2em] text-white shadow-md sm:right-[-40px] sm:top-[30px]">
                                LUNAS
                            </div>
                        </div>
                        <!-- Header -->
                        <div class="text-center mb-8">
                            <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                Terima kasih atas Order Anda
                            </h1>
                            <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3 text-gray-600 dark:text-gray-300">
                                <span class="text-sm sm:text-base">Order ID: #{{ order.order_number }}</span>
                                
                                <!-- Copy Order Number Button -->
                                <button @click="copyOrderNumber" 
                                        class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 transition-colors"
                                        :class="{ 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200': copied }">
                                    <svg v-if="!copied" class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                    <svg v-else class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    {{ copied ? 'Copied!' : 'Copy' }}
                                </button>
                            </div>
                            <span class="text-sm sm:text-base text-gray-600 dark:text-gray-300">{{ formatDateTime(order.created_at) }}</span>
                        </div>
                        <!-- Order Status Alert -->
                        <div class="mb-6">
                            <!-- Pending Payment -->
                                                        <div v-if="isManualQris && order.status === 'pending' && order.payment_status === 'unpaid' && !isExpired"
                                 class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                                    <div class="flex-1">
                                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                            Menunggu Pembayaran QRIS
                                        </h3>
                                        <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                                            Bayar {{ formatPrice(order.total_amount) }} sebelum {{ formatDateTime(order.expires_at) }}, lalu tekan "Saya Sudah Bayar".
                                        </p>
                                    </div>
                                    <button type="button" @click="showQris = true" class="shrink-0 rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                                        Tampilkan QRIS
                                    </button>
                                </div>
                            </div>

                            <div v-else-if="isManualQris && order.status === 'pending' && order.payment_status === 'pending'"
                                 class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">
                                    Menunggu Konfirmasi Admin
                                </h3>
                                <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                                    Pembayaran Anda sedang diperiksa. Status pesanan akan diperbarui setelah admin mengonfirmasi.
                                </p>
                            </div>

                            <div v-else-if="order.status === 'pending' && order.payment_status === 'pending' && !isExpired" 
                                 class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-yellow-600 dark:text-yellow-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <h3 class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                                            Menunggu Pembayaran
                                        </h3>
                                        <p class="text-sm text-yellow-700 dark:text-yellow-300 mt-1">
                                            Lakukan pembayaran sebelum {{ formatDateTime(order.expires_at) }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Expired -->
                            <div v-else-if="isExpired && order.status !== 'paid'" 
                                 class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                            Pembayaran Expired
                                        </h3>
                                        <p class="text-sm text-red-700 dark:text-red-300 mt-1">
                                            Batas waktu pembayaran telah berakhir.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Paid -->
                            <div v-if="(order.status === 'paid' || order.payment_status === 'paid') && showPaymentSuccessNotification" 
                                 class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4 relative">
                                <button @click="showPaymentSuccessNotification = false" 
                                        class="absolute top-2 right-2 text-green-600 dark:text-green-400 hover:text-green-800 dark:hover:text-green-200 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                                <div class="flex items-center pr-6">
                                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div class="flex-1">
                                        <h3 class="text-sm font-medium text-green-800 dark:text-green-200">
                                            Pembayaran Berhasil
                                        </h3>
                                        <p class="text-sm text-green-700 dark:text-green-300 mt-1">
                                            Terima kasih, pembayaran Anda telah kami terima.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- DIGITAL DOWNLOADS SECTION - PRIORITAS UTAMA -->
                        <div v-if="hasDigitalProducts && (order.status === 'paid' || order.payment_status === 'paid')" 
                             class="rounded-lg shadow-sm border p-6 mb-6"
                             :class="completedDeliveries.length > 0 ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800'">
                            
                            <!-- Header untuk delivery sedang diproses -->
                            <div v-if="completedDeliveries.length === 0 && !loadingDeliveries" class="mb-6">
                                <div class="flex items-start">
                                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <h3 class="text-lg font-semibold text-yellow-800 dark:text-yellow-200 mb-2">File Digital Sedang Diproses...</h3>
                                        <p class="text-sm text-yellow-700 dark:text-yellow-300 mb-3">
                                            Pembayaran Anda telah berhasil! File digital sedang diproses dan akan tersedia dalam beberapa saat.
                                        </p>
                                        <button @click="refreshDeliveries" 
                                                class="inline-flex items-center px-4 py-2 border border-yellow-300 dark:border-yellow-600 rounded-md text-sm font-medium text-yellow-700 dark:text-yellow-300 bg-yellow-100 dark:bg-yellow-900/50 hover:bg-yellow-200 dark:hover:bg-yellow-900/70 transition-colors">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                            Refresh Status
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Header untuk delivery yang sudah ready -->
                            <div v-else-if="completedDeliveries.length > 0 && !loadingDeliveries" class="mb-6">
                                <div class="flex items-start">
                                    <svg class="w-6 h-6 text-green-600 dark:text-green-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <div>
                                        <h3 class="text-lg font-semibold text-green-800 dark:text-green-200 mb-2">File Digital Anda Siap!</h3>
                                        <p class="text-sm text-green-700 dark:text-green-300">
                                            Produk digital Anda sudah siap untuk diunduh. Klik tombol download di bawah ini.
                                        </p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Loading State -->
                            <div v-if="loadingDeliveries" class="text-center py-8">
                                <svg class="animate-spin w-8 h-8 text-blue-600 dark:text-blue-400 mx-auto mb-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <p class="text-gray-600 dark:text-gray-300">Memproses file digital Anda...</p>
                            </div>

                            <!-- Digital Downloads -->
                            <div v-else-if="completedDeliveries.length > 0 || deliveries.length > 0" class="space-y-4">
                                <div v-for="delivery in (completedDeliveries.length > 0 ? completedDeliveries : deliveries)" :key="delivery.id"
                                     class="border rounded-lg p-4 delivery-card transition-all duration-200"
                                     :class="getDeliveryCardClass(delivery)">
                                    
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1">
                                            <h4 class="text-lg font-medium text-gray-900 dark:text-white">
                                                {{ delivery.product_name }}
                                            </h4>
                                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ delivery.variant_name }}</p>
                                        </div>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium status-badge"
                                              :class="getDeliveryStatusClass(delivery.delivery_status || delivery.status)">
                                            {{ getDeliveryStatusText(delivery.delivery_status || delivery.status) }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-3 gap-4 mb-4">
                                        <div class="text-center p-3 bg-green-50 dark:bg-gray-700 rounded">
                                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                                {{ delivery.download_count || 0 }}/{{ delivery.max_downloads || 3 }}
                                            </div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Downloads</div>
                                        </div>
                                        <div class="text-center p-3 bg-green-50 dark:bg-gray-700 rounded">
                                            <div class="text-lg font-semibold" 
                                                 :class="(delivery.remaining_downloads || 3) > 0 ? 'text-green-600 dark:text-green-400' : 'text-green-600 dark:text-green-400'">
                                                {{ delivery.remaining_downloads || 0 }}
                                            </div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Tersisa</div>
                                        </div>
                                        <div class="text-center p-3 rounded"
                                             :class="isDeliveryExpired(delivery) 
                                                ? 'bg-red-50 dark:bg-red-900/20' 
                                                : 'bg-green-50 dark:bg-gray-700'">
                                            <div class="text-sm font-medium"
                                                 :class="isDeliveryExpired(delivery) 
                                                    ? 'text-red-600 dark:text-red-400' 
                                                    : 'text-green-600 dark:text-green-400'">
                                                {{ isDeliveryExpired(delivery) ? 'Expired' : 'Aktif' }}
                                            </div>
                                            <div class="text-xs"
                                                 :class="isDeliveryExpired(delivery) 
                                                    ? 'text-red-600 dark:text-red-400' 
                                                    : 'text-gray-600 dark:text-gray-400'">
                                                {{ formatDateTime(delivery.expires_at) }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Expired Warning Alert -->
                                    <div v-if="isDeliveryExpired(delivery)" 
                                         class="mb-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                                        <div class="flex items-start">
                                            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                            <div class="flex-1">
                                                <h4 class="text-sm font-semibold text-red-800 dark:text-red-200 mb-1">
                                                    Link Download File sudah expired
                                                </h4>
                                                <p class="text-sm text-red-700 dark:text-red-300">
                                                    Silahkan hubungi Admin untuk mendapatkan link download baru.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex gap-3">
                                        <!-- Download Button - Only show if can download -->
                                        <button v-if="canDownloadFile(delivery)"
                                                @click="downloadFile(delivery)"
                                                :disabled="downloading === delivery.id || isDeliveryExpired(delivery)"
                                                class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                            <svg v-if="downloading === delivery.id" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                            </svg>
                                            <span v-if="downloading === delivery.id">Downloading...</span>
                                            <span v-else>Download File</span>
                                        </button>

                                        <!-- Expired Button - Show when expired -->
                                        <button v-else-if="isDeliveryExpired(delivery)"
                                                disabled
                                                class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-500 cursor-not-allowed opacity-75">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                            </svg>
                                            Link Expired
                                        </button>

                                        <!-- Download Limit Reached Button -->
                                        <button v-else-if="isDownloadLimitReached(delivery)"
                                                disabled
                                                class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-gray-400 cursor-not-allowed opacity-60">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                            </svg>
                                            Batas Download Tercapai
                                        </button>

                                        <!-- Pending/Processing Button -->
                                        <button v-else
                                                disabled
                                                class="flex-1 inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-yellow-500 cursor-not-allowed opacity-75">
                                            <svg class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Sedang Diproses
                                        </button>

                                        <!-- Check Status Button -->
                                        <button @click="checkDeliveryStatus(delivery)"
                                                :disabled="checking === delivery.id"
                                                class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 transition-colors">
                                            <svg v-if="checking === delivery.id" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                                            </svg>
                                        </button>
                                    </div>

                                    <div v-if="delivery.download_count > 0" class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            <span>Terakhir diunduh: {{ formatDateTime(delivery.delivered_at) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section untuk belum ada pembayaran -->
                        <div v-else-if="hasDigitalProducts && (order.status !== 'paid' && order.payment_status !== 'paid')" 
                             class="bg-gray-50 dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                            <div class="text-center py-8">
                                <svg class="w-16 h-16 text-gray-400 dark:text-gray-500 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">File Digital Menunggu Pembayaran</h3>
                                <p class="text-gray-600 dark:text-gray-400 mb-2">File digital akan tersedia setelah pembayaran</p>
                                <p class="text-sm text-gray-500 dark:text-gray-500">
                                    Silakan selesaikan pembayaran untuk mengakses file digital Anda.
                                </p>
                            </div>
                        </div>

                        <!-- Payment Gateway Button -->
                        <div v-if="order.status === 'pending' && order.payment_status === 'pending' && !isExpired && order.snap_token" 
                             class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6 text-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                Lanjutkan Pembayaran
                            </h3>
                            <button @click="payNow" 
                                    :disabled="paying"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                <svg v-if="paying" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 718-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                                <span v-if="paying">Memproses...</span>
                                <span v-else">Bayar Sekarang</span>
                            </button>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-3">
                                Klik tombol di atas untuk melanjutkan pembayaran
                            </p>
                        </div>

                        <!-- Order Summary - Compact Single Section -->
                        <div v-if="order && order.order_items && order.order_items.length > 0" class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
                            <!-- Order Items -->
                            <div class="mb-6">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Item Pesanan</h3>
                                <div class="space-y-2">
                                    <div v-for="item in order.order_items" :key="item.id" 
                                         class="flex items-center justify-between py-2 px-3 bg-gray-50 dark:bg-gray-700 rounded">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-medium text-gray-900 dark:text-white">
                                                {{ item.product_name }}
                                            </h4>
                                            <p class="text-xs text-gray-600 dark:text-gray-300">
                                                {{ item.variant_name }}
                                            </p>
                                            <div v-if="item.product_variant?.product?.is_digital" 
                                                 class="mt-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200">
                                                    <svg class="w-2.5 h-2.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                                    </svg>
                                                    Digital
                                                </span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ item.formatted_total }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer Information -->
                            <div class="mb-6 pb-6 border-b border-gray-200 dark:border-gray-600">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Informasi Customer</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Nama:</span>
                                        <span class="text-gray-900 dark:text-white font-medium">{{ order.customer_name }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Email:</span>
                                        <span class="text-gray-900 dark:text-white font-medium">{{ maskEmail(order.customer_email) }}</span>
                                    </div>
                                    <div v-if="order.customer_phone" class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Telepon:</span>
                                        <span class="text-gray-900 dark:text-white font-medium">{{ maskPhone(order.customer_phone) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Pembayaran:</span>
                                        <span class="text-gray-900 dark:text-white font-medium">{{ order.payment_method?.name || 'Midtrans' }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Summary -->
                            <div class="space-y-2">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-3">Ringkasan Pembayaran</h3>
                                
                                <!-- Subtotal -->
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                                    <span class="text-gray-900 dark:text-white">{{ formatPrice(order.total_amount - (order.fee_amount || 0) - (order.unique_code || 0)) }}</span>
                                </div>

                                <!-- Voucher Discount (if exists) -->
                                <div v-if="order.discount_amount && order.discount_amount > 0" class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Voucher Diskon</span>
                                    <span class="font-medium text-green-600 dark:text-green-400">-{{ formatPrice(order.discount_amount) }}</span>
                                </div>
                                
                                <!-- Fee Platform -->
                                <div v-if="order.fee_amount && order.fee_amount > 0" class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Fee Platform</span>
                                    <span class="text-gray-900 dark:text-white">{{ formatPrice(order.fee_amount) }}</span>
                                </div>

                                <div v-if="order.unique_code" class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Kode Unik</span>
                                    <span class="text-gray-900 dark:text-white">{{ formatPrice(order.unique_code) }}</span>
                                </div>
                                
                                <!-- Total -->
                                <div class="flex justify-between items-center pt-2 border-t border-gray-200 dark:border-gray-600">
                                    <span class="text-base font-semibold text-gray-900 dark:text-white">Total Pembayaran</span>
                                    <span class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ formatPrice(order.total_amount) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Gateway Button -->
                        <div v-if="order.status === 'pending' && order.payment_status === 'pending' && !isExpired && order.snap_token" 
                             class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6 text-center">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">
                                Lanjutkan Pembayaran
                            </h3>
                            <button @click="payNow" 
                                    :disabled="paying"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-3 border border-transparent rounded-lg shadow-sm text-base font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                                <svg v-if="paying" class="animate-spin w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <svg v-else class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                </svg>
                                <span v-if="paying">Memproses...</span>
                                <span v-else>Bayar Sekarang</span>
                            </button>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-3">
                                Klik tombol di atas untuk melanjutkan pembayaran
                            </p>
                        </div>


                        <!-- Help Section -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 sm:p-6">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <div class="ml-3 min-w-0 flex-1">
                                        <h3 class="text-lg font-medium text-blue-800 dark:text-blue-200 mb-2">
                                            Butuh Bantuan?
                                        </h3>
                                        <p class="text-blue-700 dark:text-blue-300 mb-4 text-sm sm:text-base">
                                            Jika Anda mengalami kesulitan atau memiliki pertanyaan tentang pesanan ini, 
                                            jangan ragu untuk menghubungi tim customer service kami.
                                        </p>
                                        <div class="grid grid-cols-2 gap-3">
                                            <a :href="`mailto:${$page.props.settings?.contact?.email || 'admin@digitalpay.web.id'}`"
                                               class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-lg bg-blue-600 px-2 py-2.5 text-sm font-medium text-white transition-colors hover:bg-blue-700 sm:px-4">
                                                <IconifyIcon icon="lucide:mail" width="18" class="shrink-0" />
                                                Email Support
                                            </a>
                                            <a :href="whatsappSupportUrl" target="_blank" rel="noopener"
                                               class="inline-flex items-center justify-center gap-1.5 whitespace-nowrap rounded-lg bg-green-600 px-2 py-2.5 text-sm font-medium text-white transition-colors hover:bg-green-700 sm:px-4">
                                                <IconifyIcon icon="simple-icons:whatsapp" width="18" class="shrink-0" />
                                                WhatsApp
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Success Modal -->
                    <div v-if="showSuccessModal" class="fixed inset-0 bg-black bg-opacity-50 dark:bg-opacity-70 flex items-center justify-center z-50">
                        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-sm mx-4 border border-gray-200 dark:border-gray-700">
                            <div class="text-center">
                                <svg class="w-16 h-16 text-green-600 dark:text-green-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Download Berhasil!</h3>
                                <p class="text-gray-600 dark:text-gray-300 mb-4">File telah diunduh ke perangkat Anda</p>
                                <button @click="showSuccessModal = false" 
                                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                                    OK
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QRIS MODAL -->
        <Teleport to="body">
            <div v-if="showQris && isManualQris && order.payment_status === 'unpaid' && !isExpired"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 px-4"
                 role="dialog" aria-modal="true" aria-labelledby="qris-title"
                 @click.self="showQris = false">
                <div class="relative max-h-[95vh] w-full max-w-sm overflow-y-auto rounded-2xl bg-white p-6 text-center shadow-2xl dark:bg-gray-800">
                    <button type="button" @click="showQris = false" class="absolute right-4 top-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" aria-label="Tutup">
                        <IconifyIcon icon="lucide:x" width="22" />
                    </button>
                    <h2 id="qris-title" class="text-lg font-semibold text-gray-900 dark:text-white">Bayar dengan QRIS</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Pindai dengan aplikasi bank atau e-wallet apa pun.</p>

                    <img :src="order.manual_qris_image" alt="Kode QRIS pembayaran" width="244" height="244"
                         class="qris-image mx-auto mt-4 h-[244px] w-[244px] rounded-lg bg-white" />

                    <div class="mt-4 rounded-xl bg-gray-50 p-3 dark:bg-gray-700">
                        <p class="text-xs text-gray-600 dark:text-gray-300">Total yang harus dibayar</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatPrice(order.total_amount) }}</p>
                        <p v-if="order.unique_code" class="mt-1 text-xs text-gray-600 dark:text-gray-300">
                            Ketik nominal tepat, termasuk kode unik {{ order.unique_code }}, agar pembayaran mudah dicocokkan.
                        </p>
                    </div>

                    <a :href="order.manual_qris_image" :download="`QRIS-${order.order_number}.webp`" class="mt-3 inline-block text-sm font-medium text-blue-600 hover:underline dark:text-blue-400">
                        Unduh gambar QRIS
                    </a>

                    <button type="button" @click="confirmManualPayment" :disabled="confirmingPayment"
                            class="mt-4 w-full rounded-xl bg-blue-600 py-3 font-semibold text-white hover:bg-blue-700 disabled:opacity-60">
                        {{ confirmingPayment ? 'Memproses...' : 'Saya Sudah Bayar' }}
                    </button>
                    <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">Tekan setelah pembayaran berhasil di aplikasi Anda.</p>
                </div>
            </div>
        </Teleport>
    </AppLayout>
    <div v-else class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900">
        <div class="text-center">
            <svg class="animate-spin w-12 h-12 text-blue-600 dark:text-blue-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="text-gray-600 dark:text-gray-300">Memuat invoice...</p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, computed } from 'vue'
import { Head, usePage, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import axios from 'axios'

const props = defineProps({
    order: {
        type: Object,
        required: false,
        default: () => null
    },
    hasDigitalProducts: {
        type: Boolean,
        default: false
    },
    digitalDeliveries: {
        type: Array,
        default: () => []
    },
    settings: {
        type: Object,
        default: () => ({})
    }
})

const $page = usePage()

const deliveries = ref(Array.isArray(props.digitalDeliveries) ? [...props.digitalDeliveries] : [])
const downloading = ref(null)
const checking = ref(null)
const loadingDeliveries = ref(false)
const showSuccessModal = ref(false)
const paying = ref(false)
const reloadProgress = ref(100)
const showCustomerInfo = ref(false)
const copied = ref(false)
const showPaymentSuccessNotification = ref(true)
const isManualQris = computed(() => props.order?.payment_gateway === 'manual_qris')
const isLunas = computed(() => props.order?.status === 'paid' || props.order?.payment_status === 'paid')
const showQris = ref(isManualQris.value && props.order?.status === 'pending' && props.order?.payment_status === 'unpaid')
const confirmingPayment = ref(false)

const confirmManualPayment = () => {
    confirmingPayment.value = true
    router.post(`/invoice/${props.order.order_number}/manual-paid`, {}, {
        preserveScroll: true,
        onFinish: () => {
            confirmingPayment.value = false
        },
    })
}

let statusCheckInterval = null
let pageReloadInterval = null

const isExpired = computed(() => {
    if (!props.order || !props.order.expires_at) return false
    return new Date(props.order.expires_at) < new Date()
})

const supportEmail = computed(() => {
    return $page.props.settings?.contact?.email || 'admin@digitalpay.web.id'
})

const whatsappUrl = computed(() => {
    const whatsappNumber = $page.props.settings?.contact?.whatsapp || '6281234567890'
    const message = encodeURIComponent(`Halo, saya butuh bantuan terkait order #${props.order.order_number}`)
    return `https://wa.me/${whatsappNumber}?text=${message}`
})

const whatsappSupportUrl = computed(() => {
    const whatsappNumber = $page.props.settings?.contact?.whatsapp || '6281234567890'
    const orderNumber = props.order?.order_number || 'N/A'
    const message = encodeURIComponent(`Halo, saya butuh bantuan terkait order #${orderNumber}`)
    return `https://wa.me/${whatsappNumber}?text=${message}`
})

const isDeliveryExpired = computed(() => {
    return (delivery) => {
        if (!delivery) return false
        
        if (delivery.is_expired === true) {
            return true
        }
        
        if (delivery.expires_at) {
            const expiryDate = new Date(delivery.expires_at)
            const now = new Date()
            return expiryDate < now
        }
        
        return false
    }
})

const canDownloadFile = computed(() => {
    return (delivery) => {
        if (!delivery) return false
        
        if (isDeliveryExpired.value(delivery)) {
            return false
        }
        
        const isCompleted = delivery.delivery_status === 'completed' || delivery.status === 'completed'
        if (!isCompleted) {
            return false
        }
        
        const downloadCount = delivery.download_count || 0
        const maxDownloads = delivery.max_downloads || 3
        if (downloadCount >= maxDownloads) {
            return false
        }
        
        if (delivery.can_download === false) {
            return false
        }
        
        return true
    }
})

const isDownloadLimitReached = computed(() => {
    return (delivery) => {
        if (!delivery) return false
        
        if (isDeliveryExpired.value(delivery)) {
            return false
        }
        
        const downloadCount = delivery.download_count || 0
        const maxDownloads = delivery.max_downloads || 3
        return downloadCount >= maxDownloads
    }
})

const completedDeliveries = computed(() => {
    const filtered = deliveries.value.filter(delivery => {
        const statusCompleted = delivery && 
            (delivery.delivery_status === 'completed' || delivery.status === 'completed')
        
        return statusCompleted
    })
    
    return filtered
})

const hasReadyDigitalProducts = computed(() => {
    const hasProducts = props.hasDigitalProducts
    const isPaid = props.order?.status === 'paid' || props.order?.payment_status === 'paid'
    const hasCompleted = completedDeliveries.value.length > 0
    
    return hasProducts && isPaid && hasCompleted
})

const copyOrderNumber = async () => {
    try {
        await navigator.clipboard.writeText(props.order.order_number)
        copied.value = true
        setTimeout(() => {
            copied.value = false
        }, 2000)
    } catch (err) {
        const textArea = document.createElement('textarea')
        textArea.value = props.order.order_number
        document.body.appendChild(textArea)
        textArea.select()
        document.execCommand('copy')
        document.body.removeChild(textArea)
        copied.value = true
        setTimeout(() => {
            copied.value = false
        }, 2000)
    }
}

const payNow = () => {
    if (!props.order.snap_token) {
        alert('Payment token not available. Please refresh the page.')
        return
    }
    
    paying.value = true
    
    if (typeof window.snap !== 'undefined') {
        window.snap.pay(props.order.snap_token, {
            onSuccess: function(result) {
                paying.value = false
                setTimeout(() => {
                    window.location.reload()
                }, 2000)
            },
            onPending: function(result) {
                paying.value = false
                alert('Pembayaran sedang diproses. Kami akan mengirim konfirmasi melalui email.')
            },
            onError: function(result) {
                paying.value = false
                alert('Pembayaran gagal. Silakan coba lagi atau hubungi customer service.')
            },
            onClose: function() {
                paying.value = false
            }
        })
    } else {
        alert('Payment system not loaded. Please refresh the page.')
        paying.value = false
    }
}

const downloadFile = async (delivery) => {
    try {
        if (isDeliveryExpired.value(delivery)) {
            alert('Link Download File sudah expired. Silahkan hubungi Admin')
            return
        }
        
        if (!canDownloadFile.value(delivery)) {
            if (isDeliveryExpired.value(delivery)) {
                alert('Link Download File sudah expired. Silahkan hubungi Admin')
            } else if (isDownloadLimitReached.value(delivery)) {
                alert('Batas download telah tercapai.')
            } else {
                alert('File tidak dapat diunduh saat ini. Silakan coba lagi nanti.')
            }
            return
        }
        
        downloading.value = delivery.id
        
        try {
            const statusResponse = await axios.get(`/digital/status/${delivery.download_token}`)
            const statusData = statusResponse.data
            
            if (statusData.is_expired || !statusData.can_download) {
                if (statusData.is_expired) {
                    alert('Link Download File sudah expired. Silahkan hubungi Admin')
                } else {
                    alert('File tidak dapat diunduh saat ini. Silakan coba lagi nanti.')
                }
                downloading.value = null
                return
            }
        } catch (statusError) {
            // If status check fails, still try to download (might be network issue)
            console.warn('Status check failed, proceeding with download:', statusError)
        }
        
        if (delivery.download_url) {
            const link = document.createElement('a')
            link.href = delivery.download_url
            link.download = '' // Let server set filename via Content-Disposition
            link.target = '_blank'
            document.body.appendChild(link)
            link.click()
            document.body.removeChild(link)
            
            showSuccessModal.value = true
            
            setTimeout(() => {
                checkDeliveryStatus(delivery)
            }, 2000)
        } else {
            alert('URL download tidak tersedia. Silakan coba lagi.')
        }
        
    } catch (error) {
        console.error('Download error:', error)
        
        if (error.response && error.response.status === 410) {
            alert('Link Download File sudah expired. Silahkan hubungi Admin')
        } else if (error.response && error.response.status === 429) {
            alert('Batas download telah tercapai.')
        } else {
            alert('Terjadi kesalahan saat mengunduh file. Silakan coba lagi.')
        }
    } finally {
        downloading.value = null
    }
}

const checkDeliveryStatus = async (delivery) => {
    try {
        checking.value = delivery.id
        
        const response = await axios.get(`/digital/status/${delivery.download_token}`)
        
        const index = deliveries.value.findIndex(d => d.id === delivery.id)
        if (index !== -1) {
            deliveries.value[index] = {
                ...deliveries.value[index],
                ...response.data
            }
        }
        
    } catch (error) {
        console.error('Status check error:', error)
    } finally {
        checking.value = null
    }
}

const refreshDeliveries = async () => {
    if (!props.hasDigitalProducts) return
    
    try {
        loadingDeliveries.value = true
        
        const response = await axios.get(`/invoice/${props.order.order_number}/refresh-deliveries`)
        
        if (response.data.deliveries) {
            deliveries.value = response.data.deliveries
        }
    } catch (error) {
        console.error('Error refreshing deliveries:', error)
    } finally {
        loadingDeliveries.value = false
    }
}

const debugDigitalProducts = () => {
    if (props.hasDigitalProducts) {
        if (deliveries.value.length > 0) {
        }
    }
}

const getDeliveryCardClass = (delivery) => {
    if (isDeliveryExpired.value(delivery)) {
        return 'border-red-300 dark:border-red-800 bg-red-50 dark:bg-red-900/20'
    }
    
    if (delivery.remaining_downloads !== undefined && delivery.remaining_downloads <= 1 && delivery.remaining_downloads > 0) {
        return 'border-yellow-300 dark:border-yellow-800 bg-yellow-50 dark:bg-yellow-900/20'
    }
    
    return 'border-green-300 dark:border-green-800 bg-green-50 dark:bg-green-900/20'
}

const getDeliveryStatusClass = (status) => {
    const classes = {
        'completed': 'bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200',
        'pending': 'bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200',
        'processing': 'bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200',
        'failed': 'bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200'
    }
    return classes[status] || 'bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200'
}

const getDeliveryStatusText = (status) => {
    const statusMap = {
        'completed': 'Siap Download',
        'pending': 'Pending',
        'processing': 'Processing',
        'failed': 'Gagal'
    }
    return statusMap[status] || status
}

const formatPrice = (price) => {
    if (!price || isNaN(price)) return 'Rp 0'
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(price)
}

const formatDateTime = (date) => {
    if (!date) return '-'
    const d = new Date(date)
    const day = String(d.getDate()).padStart(2, '0')
    const month = d.toLocaleDateString('en-US', { month: 'short' })
    const year = d.getFullYear()
    const hours = String(d.getHours()).padStart(2, '0')
    const minutes = String(d.getMinutes()).padStart(2, '0')
    return `${day} ${month} ${year} ${hours}:${minutes}`
}

const maskEmail = (email) => {
    if (!email) return '-'
    const [username, domain] = email.split('@')
    if (!username || !domain) return email
    
    const maskedUsername = username.length > 2 
        ? username.substring(0, 2) + '*'.repeat(username.length - 2)
        : username
    
    return `${maskedUsername}@${domain}`
}

const maskPhone = (phone) => {
    if (!phone) return '-'
    const cleaned = phone.replace(/\D/g, '')
    
    if (cleaned.length < 4) return phone
    
    // Show first 3 digits and last 3 digits, mask the middle
    const first = cleaned.substring(0, 3)
    const last = cleaned.substring(cleaned.length - 3)
    const middle = '*'.repeat(Math.max(0, cleaned.length - 6))
    
    return `${first}${middle}${last}`
}

const startStatusChecking = () => {
    const hasPendingDeliveries = deliveries.value.some(d => 
        d.status === 'pending' || d.status === 'processing'
    )
    
    if (hasPendingDeliveries) {
        statusCheckInterval = setInterval(() => {
            deliveries.value.forEach(delivery => {
                if (delivery.status === 'pending' || delivery.status === 'processing') {
                    checkDeliveryStatus(delivery)
                }
            })
        }, 30000)
    }
}

const startPageReload = () => {
    const shouldCheckStatus = (props.order.status === 'pending' && props.order.payment_status === 'pending' && !isExpired.value) ||
                              (props.hasDigitalProducts && deliveries.value.length === 0 && props.order.payment_status === 'paid')
    
    if (shouldCheckStatus) {
        pageReloadInterval = setInterval(async () => {
            try {
                const response = await axios.get(`/api/invoice/${props.order.order_number}/status`)
                const data = response.data
                
                if (data.payment_status === 'paid' && props.order.payment_status !== 'paid') {
                    window.location.reload()
                    return
                }
                
                if (props.hasDigitalProducts && data.payment_status === 'paid' && deliveries.value.length === 0) {
                    const deliveriesResponse = await axios.get(`/invoice/${props.order.order_number}/refresh-deliveries`)
                    if (deliveriesResponse.data.deliveries && deliveriesResponse.data.deliveries.length > 0) {
                        window.location.reload()
                        return
                    }
                }
                
                if (data.is_expired && !isExpired.value) {
                    window.location.reload()
                }
                
            } catch (error) {
            }
        }, 10000) // Check every 10 seconds instead of reloading
    }
}

const simulateReload = () => {
    reloadProgress.value = 0
    const interval = setInterval(() => {
        reloadProgress.value += 5
        if (reloadProgress.value >= 100) {
            clearInterval(interval)
        }
    }, 100)
}

onMounted(() => {
    if (!props.order || !props.order.order_number) {
        console.error('Order data is not available')
        return
    }
    
    debugDigitalProducts()
    
    if (props.order.status === 'pending' && props.hasDigitalProducts) {
        simulateReload()
    }
    
    if (props.hasDigitalProducts && 
        (props.order.status === 'paid' || props.order.payment_status === 'paid') && 
        deliveries.value.length === 0) {
        refreshDeliveries()
    }
    
    startStatusChecking()
    startPageReload()
    
    if (props.order.status === 'pending' && props.order.snap_token && !window.snap) {
        const script = document.createElement('script')
        script.src = 'https://app.sandbox.midtrans.com/snap/snap.js'
        script.setAttribute('data-client-key', 'SB-Mid-client-your-sandbox-client-key')
        document.head.appendChild(script)
    }
})

onBeforeUnmount(() => {
    if (statusCheckInterval) {
        clearInterval(statusCheckInterval)
    }
    if (pageReloadInterval) {
        clearInterval(pageReloadInterval)
    }
})
</script>

<style scoped>
/* Original styles preserved */
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.delivery-card {
    transition: all 0.2s ease;
}

.delivery-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

@media (prefers-color-scheme: dark) {
    .delivery-card:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
}

button {
    transition: all 0.2s ease;
}

button:hover:not(:disabled) {
    transform: translateY(-1px);
}

@media (max-width: 640px) {
    .grid-cols-3 {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
}

button:focus {
    outline: 2px solid #3b82f6;
    outline-offset: 2px;
}

.status-badge {
    font-weight: 600;
    letter-spacing: 0.025em;
}
.qris-image {
    image-rendering: pixelated;
}
</style>
