<template>
    <AppLayout>
        <!-- Main Content -->
        <template v-if="order">
            <!-- Simple Header -->
            <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 pt-20">
                <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6 text-center">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">
                        Pembayaran
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Order ID: #{{ order.order_number }}
                    </p>
                </div>
            </div>

            <div class="max-w-4xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-8">
                <div class="space-y-4 sm:space-y-6 overflow-hidden">
                <!-- Payment Method Card - Primary Focus (Hidden if expired) -->
                <div v-if="order && order.status !== 'expired' && paymentMethod" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center space-x-4 mb-4">
                        <div class="w-24 sm:w-28 h-16 flex-shrink-0 bg-white rounded-xl flex items-center justify-center border border-gray-200 dark:border-gray-600 px-3">
                            <img
                                :src="paymentMethod?.logo"
                                :alt="paymentMethod?.display_name"
                                class="max-h-9 w-full object-contain"
                            />
                        </div>
                        <div>
                            <h2 class="text-lg sm:text-2xl font-bold text-gray-900 dark:text-white">
                                {{ paymentMethod?.display_name }}
                            </h2>
                            <p class="text-gray-600 dark:text-gray-400">
                                {{ paymentMethod?.description }}
                            </p>
                        </div>
                    </div>

                    <!-- QRIS QR Code - Prominent Display for QRIS Payment -->
                    <div v-if="qrString" class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6 border-2 border-dashed border-gray-300 dark:border-gray-600">
                        <div class="text-center">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
                                Scan QR Code untuk pembayaran
                            </p>
                            <div class="flex justify-center mb-4">
                                <div class="bg-white p-2 sm:p-4 rounded-lg shadow-sm border border-gray-200">
                                    <div ref="qrCodeContainer" class="w-40 h-40 sm:w-48 sm:h-48"></div>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Silahkan scan QR Code di atas
                            </p>
                        </div>
                    </div>

                    <!-- Virtual Account Number - Prominent Display -->
                    <div v-else-if="paymentInstructions && (paymentInstructions.va_number || paymentInstructions.bill_key)" class="bg-gray-50 dark:bg-gray-700 rounded-xl p-6 border-2 border-dashed border-gray-300 dark:border-gray-600">
                        <div class="text-center">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nomor Virtual Account
                            </p>
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-600 mb-4">
                                <p class="text-lg sm:text-xl font-mono font-bold text-gray-900 dark:text-white tracking-wider break-all">
                                    {{ paymentInstructions.va_number || paymentInstructions.bill_key }}
                                </p>
                            </div>
                            <button 
                                @click="copyToClipboard(paymentInstructions.va_number || paymentInstructions.bill_key, 'va')"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                            >
                                <IconifyIcon icon="lucide:copy" width="16" class="mr-2" />
                                Salin
                            </button>
                        </div>
                    </div>

                    <!-- Mandiri e-Channel (if available) -->
                    <div v-if="paymentInstructions && paymentInstructions.biller_code" class="mt-4 bg-gray-50 dark:bg-gray-700 rounded-xl p-6 border-2 border-dashed border-gray-300 dark:border-gray-600">
                        <div class="text-center">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Kode Biller
                            </p>
                            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-600 mb-4">
                                <p class="text-xl sm:text-2xl font-mono font-bold text-gray-900 dark:text-white tracking-wider break-all">
                                    {{ paymentInstructions.biller_code }}
                                </p>
                            </div>
                            <button 
                                @click="copyToClipboard(paymentInstructions.biller_code, 'biller')"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors"
                            >
                                <IconifyIcon icon="lucide:copy" width="16" class="mr-2" />
                                Salin
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Payment Status Card -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">
                            Status
                        </h3>
                        <div :class="[
                            'px-3 py-1 rounded-full text-sm font-medium',
                            order ? getStatusColor(order.payment_status) : 'bg-gray-100 text-gray-800'
                        ]">
                            {{ order ? getStatusText(order.payment_status) : 'Loading...' }}
                        </div>
                    </div>
                    
                    <!-- Timer -->
                    <div v-if="order && order.expires_at && !isExpired" class="mb-4">
                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-lg p-4">
                            <div class="flex items-center justify-center space-x-2">
                                <IconifyIcon icon="lucide:timer" width="20" class="text-amber-600 dark:text-amber-400" />
                                <span class="text-amber-800 dark:text-amber-200 font-semibold">
                                    Berakhir dalam: {{ countdownText }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Check Status Button or Order Again Button -->
                    <button 
                        v-if="order && order.status !== 'expired'"
                        @click="checkPaymentStatus"
                        :disabled="checkingStatus"
                        class="w-full inline-flex items-center justify-center px-4 sm:px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white text-sm sm:text-base font-semibold rounded-lg transition-colors"
                    >
                        <IconifyIcon :icon="checkingStatus ? 'lucide:loader-2' : 'lucide:refresh-cw'" :class="{'animate-spin': checkingStatus}" width="18" class="mr-2" />
                        {{ checkingStatus ? 'Mengecek Status...' : 'Cek Status Pembayaran' }}
                    </button>
                    
                    <!-- Order Again Button for Expired Orders -->
                    <a 
                        v-else
                        :href="getProductUrl()"
                        class="w-full inline-flex items-center justify-center px-4 sm:px-6 py-3 bg-green-600 hover:bg-green-700 text-white text-sm sm:text-base font-semibold rounded-lg transition-colors"
                    >
                        <IconifyIcon icon="lucide:shopping-cart" width="18" class="mr-2" />
                        Order Lagi
                    </a>
                </div>

                <!-- Payment Instructions (Collapsible) - Hidden if expired -->
                <div v-if="paymentInstructions && order && order.status !== 'expired'" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <!-- Toggle Header -->
                    <button 
                        @click="showInstructions = !showInstructions"
                        class="w-full p-6 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors"
                    >
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-blue-100 dark:bg-blue-900/30 rounded-full flex items-center justify-center">
                                <IconifyIcon icon="lucide:help-circle" width="20" class="text-blue-600 dark:text-blue-400" />
                            </div>
                            <p class="text-black-700 dark:text-gray-300">Cara Pembayaran</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ showInstructions ? '' : '' }}
                            </span>
                            <IconifyIcon 
                                :icon="showInstructions ? 'lucide:chevron-up' : 'lucide:chevron-down'" 
                                width="20" 
                                class="text-gray-400 transition-transform"
                            />
                        </div>
                    </button>

                    <!-- Instructions Content -->
                    <div v-show="showInstructions" class="border-t border-gray-200 dark:border-gray-700 p-6">
                        <div class="space-y-6">
                            <!-- ATM Instructions -->
                            <div v-if="paymentInstructions && paymentInstructions.atm && paymentInstructions.atm.length > 0" class="space-y-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white flex items-center">
                                    <IconifyIcon icon="lucide:credit-card" width="20" class="mr-2 text-blue-600" />
                                    Melalui ATM
                                </h3>
                                <ol class="space-y-2 list-decimal list-inside text-sm text-gray-700 dark:text-gray-300">
                                    <li v-for="(step, index) in paymentInstructions.atm" :key="index">{{ step }}</li>
                                </ol>
                            </div>

                            <!-- Internet Banking Instructions -->
                            <div v-if="paymentInstructions && paymentInstructions.internet_banking && paymentInstructions.internet_banking.length > 0" class="space-y-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white flex items-center">
                                    <IconifyIcon icon="lucide:globe" width="20" class="mr-2 text-green-600" />
                                    Melalui Internet Banking
                                </h3>
                                <ol class="space-y-2 list-decimal list-inside text-sm text-gray-700 dark:text-gray-300">
                                    <li v-for="(step, index) in paymentInstructions.internet_banking" :key="index">{{ step }}</li>
                                </ol>
                            </div>

                            <!-- Mobile Banking Instructions -->
                            <div v-if="paymentInstructions && paymentInstructions.mobile_banking && paymentInstructions.mobile_banking.length > 0" class="space-y-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white flex items-center">
                                    <IconifyIcon icon="lucide:smartphone" width="20" class="mr-2 text-purple-600" />
                                    Melalui Mobile Banking
                                </h3>
                                <ol class="space-y-2 list-decimal list-inside text-sm text-gray-700 dark:text-gray-300">
                                    <li v-for="(step, index) in paymentInstructions.mobile_banking" :key="index">{{ step }}</li>
                                </ol>
                            </div>

                            <!-- SMS Banking Instructions -->
                            <div v-if="paymentInstructions && paymentInstructions.sms_banking && paymentInstructions.sms_banking.length > 0" class="space-y-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white flex items-center">
                                    <IconifyIcon icon="lucide:message-square" width="20" class="mr-2 text-orange-600" />
                                    Melalui SMS Banking
                                </h3>
                                <ol class="space-y-2 list-decimal list-inside text-sm text-gray-700 dark:text-gray-300">
                                    <li v-for="(step, index) in paymentInstructions.sms_banking" :key="index">{{ step }}</li>
                                </ol>
                            </div>

                            <!-- QRIS Instructions -->
                            <div v-if="qrString" class="space-y-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white flex items-center">
                                    <IconifyIcon icon="lucide:qr-code" width="20" class="mr-2 text-indigo-600" />
                                    Cara Pembayaran QRIS
                                </h3>
                                <div class="bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-lg p-4">
                                    <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                                        <div class="flex items-start space-x-2">
                                            <span class="font-semibold text-indigo-600 dark:text-indigo-400">1.</span>
                                            <span>Buka aplikasi e-wallet favorit Anda (GoPay, DANA, OVO, LinkAja, ShopeePay, dll)</span>
                                        </div>
                                        <div class="flex items-start space-x-2">
                                            <span class="font-semibold text-indigo-600 dark:text-indigo-400">2.</span>
                                            <span>Pilih menu "Scan QR" atau "Bayar"</span>
                                        </div>
                                        <div class="flex items-start space-x-2">
                                            <span class="font-semibold text-indigo-600 dark:text-indigo-400">3.</span>
                                            <span>Arahkan kamera ke QR code yang ditampilkan di atas</span>
                                        </div>
                                        <div class="flex items-start space-x-2">
                                            <span class="font-semibold text-indigo-600 dark:text-indigo-400">4.</span>
                                            <span>Konfirmasi detail pembayaran dan nominal</span>
                                        </div>
                                        <div class="flex items-start space-x-2">
                                            <span class="font-semibold text-indigo-600 dark:text-indigo-400">5.</span>
                                            <span>Masukkan PIN dan lakukan pembayaran</span>
                                        </div>
                                        <div class="flex items-start space-x-2">
                                            <span class="font-semibold text-indigo-600 dark:text-indigo-400">6.</span>
                                            <span>Pembayaran akan diproses secara otomatis</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Default Instructions for Bank Transfer -->
                            <div v-if="!hasAnyInstructions" class="space-y-4">
                                <h3 class="font-semibold text-gray-900 dark:text-white flex items-center">
                                    Cara Pembayaran {{ paymentMethod.display_name }}
                                </h3>
                                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                                    <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300">
                                        <div class="flex items-start space-x-2">
                                            <span class="font-semibold text-blue-600 dark:text-blue-400">1.</span>
                                            <span>Masuk ke aplikasi mobile banking atau internet banking {{ paymentMethod.display_name }}</span>
                                        </div>
                                        <div class="flex items-start space-x-2">
                                            <span class="font-semibold text-blue-600 dark:text-blue-400">2.</span>
                                            <span>Pilih menu Transfer atau Bayar</span>
                                        </div>
                                        <div class="flex items-start space-x-2">
                                            <span class="font-semibold text-blue-600 dark:text-blue-400">3.</span>
                                            <span>Pilih Virtual Account atau Transfer Bank</span>
                                        </div>
                                        <div class="flex items-start space-x-2">
                                            <span class="font-semibold text-blue-600 dark:text-blue-400">4.</span>
                                            <span>Masukkan nomor Virtual Account yang telah disediakan</span>
                                        </div>
                                        <div class="flex items-start space-x-2">
                                            <span class="font-semibold text-blue-600 dark:text-blue-400">5.</span>
                                            <span>Konfirmasi detail pembayaran dan nominal yang akan dibayar</span>
                                        </div>
                                        <div class="flex items-start space-x-2">
                                            <span class="font-semibold text-blue-600 dark:text-blue-400">6.</span>
                                            <span>Lakukan pembayaran dan simpan bukti transaksi</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Summary -->
                <div v-if="order" class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white mb-4">
                        Ringkasan Pesanan
                    </h3>

                    <!-- Order Date -->
                        <div v-if="order.created_at" class="flex items-center justify-between mt-4 pt-2 border-t border-gray-100 dark:border-gray-700 text-sm text-gray-600 dark:text-gray-400">
                            <span>Tanggal</span>
                            <span>{{ formatDate(order.created_at) }}</span>
                        </div>
                    
                    <!-- Order Items -->
                    <div v-if="order.items && order.items.length > 0" class="space-y-3 mt-4 mb-4">
                        <div v-for="item in order.items" :key="item.id" class="flex items-center justify-between">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900 dark:text-white text-sm">
                                    {{ item.product_name }}
                                </h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ item.variant_name }} × {{ item.quantity }}
                                </p>
                            </div>
                            <div class="font-medium text-gray-900 dark:text-white text-sm">
                                {{ formatPrice(item.total) }}
                            </div>
                        </div>
                    </div>

                    <!-- Order Total -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-3">
                        <!-- Subtotal -->
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Subtotal</span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ formatPrice(order.subtotal || order.total_amount) }}
                            </span>
                        </div>

                        <!-- Voucher Discount (if exists) -->
                        <div v-if="order.discount_amount && order.discount_amount > 0" class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Voucher Diskon</span>
                            <span class="font-medium text-green-600 dark:text-green-400">
                                -{{ formatPrice(order.discount_amount) }}
                            </span>
                        </div>
                        
                        <!-- Payment Fee (if exists) -->
                        <div v-if="order.fee_amount && order.fee_amount > 0" class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">
                                Fee Platform
                            </span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ formatPrice(order.fee_amount) }}
                            </span>
                        </div>
                        
                        <!-- Platform Fee (if different from payment fee) -->
                        <div v-if="calculatePlatformFee() > 0" class="flex items-center justify-between text-sm">
                            <span class="text-gray-600 dark:text-gray-400">Biaya Platform</span>
                            <span class="font-medium text-gray-900 dark:text-white">
                                {{ formatPrice(calculatePlatformFee()) }}
                            </span>
                        </div>
                        
                        <!-- Total Divider -->
                        <div class="border-t border-gray-200 dark:border-gray-700 pt-3">
                            <div class="flex items-center justify-between">
                                <span class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">Total Pembayaran</span>
                                <span class="text-lg sm:text-xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ formatPrice(order.total || order.total_amount) }}
                                </span>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notifications - Top Right -->
        <div class="fixed top-20 right-4 z-50 max-w-sm space-y-2">
            <TransitionGroup
                enter-active-class="transform transition-all duration-500 ease-out"
                enter-from-class="translate-x-full opacity-0 scale-90"
                enter-to-class="translate-x-0 opacity-100 scale-100"
                leave-active-class="transform transition-all duration-300 ease-in"
                leave-from-class="translate-x-0 opacity-100 scale-100"
                leave-to-class="translate-x-full opacity-0 scale-90"
                move-class="transition-transform duration-300"
            >
                <div
                    v-for="notification in visibleNotifications"
                    :key="notification.id"
                    :class="[
                        'p-4 rounded-lg shadow-2xl border-l-4 backdrop-blur-sm',
                        'bg-white/95 dark:bg-gray-800/95 border-r border-t border-b border-gray-200 dark:border-gray-700',
                        notification.type === 'success' ? 'border-l-green-500' :
                        notification.type === 'error' ? 'border-l-red-500' :
                        'border-l-blue-500'
                    ]"
                >
                    <div class="flex items-start space-x-3">
                        <div :class="[
                            'flex-shrink-0 w-7 h-7 rounded-full flex items-center justify-center mt-0.5',
                            notification.type === 'success' ? 'bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400' :
                            notification.type === 'error' ? 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400' :
                            'bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400'
                        ]">
                            <IconifyIcon 
                                :icon="notification.type === 'success' ? 'lucide:check' : 
                                       notification.type === 'error' ? 'lucide:x' : 'lucide:info'"
                                width="16"
                            />
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white leading-relaxed">
                                {{ notification.message }}
                            </p>
                        </div>
                        <button
                            @click="removeNotification(notification.id)"
                            :class="[
                                'flex-shrink-0 w-6 h-6 rounded-full flex items-center justify-center mt-1',
                                'hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-200',
                                'text-gray-400 hover:text-gray-600 dark:hover:text-gray-300'
                            ]"
                        >
                            <IconifyIcon icon="lucide:x" width="14" />
                        </button>
                    </div>
                </div>
            </TransitionGroup>
        </div>

        <!-- Redirect Overlay -->
        <Transition
            enter-active-class="transition-opacity duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-300"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="isRedirecting" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl p-8 mx-4 max-w-sm w-full text-center">
                    <div class="mb-4">
                        <div class="w-16 h-16 bg-green-100 dark:bg-green-900/30 rounded-full flex items-center justify-center mx-auto mb-4">
                            <IconifyIcon icon="lucide:check-circle" width="32" class="text-green-600 dark:text-green-400" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Pembayaran Berhasil!
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Halaman dialihkan...
                        </p>
                    </div>
                    <div class="flex items-center justify-center space-x-2">
                        <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0ms"></div>
                        <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 150ms"></div>
                        <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 300ms"></div>
                    </div>
                </div>
            </div>
        </Transition>
        </template>
    </AppLayout>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed, nextTick, Transition, TransitionGroup } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import IconifyIcon from '@/Components/IconifyIcon.vue'
import QRCode from 'qrcode'

const props = defineProps({
    order: {
        type: Object,
        default: () => null
    },
    paymentMethod: {
        type: Object,
        default: () => null
    },
    paymentInstructions: {
        type: Object,
        default: () => null
    },
    qrString: {
        type: String,
        default: () => null
    }
})

const showInstructions = ref(false)
const checkingStatus = ref(false)
const qrCodeContainer = ref(null)
const notifications = ref([])
const isRedirecting = ref(false)
const qrCodeGenerated = ref(false)
const qrCodeLoading = ref(false)
const statusPollingInterval = ref(null)
const countdownText = ref('')
const countdownInterval = ref(null)
const autoReloadInterval = ref(null)
const isExpired = ref(false)

const showNotification = (message, type = 'info') => {
    if (notifications.value.length >= 5) {
        removeNotification(notifications.value[0].id)
    }
    
    const id = Date.now() + Math.random()
    const notification = {
        id,
        message,
        type,
        timestamp: Date.now()
    }
    
    notifications.value.push(notification)
    
    setTimeout(() => {
        removeNotification(id)
    }, 5000)
}

const removeNotification = (id) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
        notifications.value.splice(index, 1)
    }
}

const visibleNotifications = computed(() => {
    return notifications.value
})

const copyToClipboard = async (text, type) => {
    if (!text) {
        showNotification('Tidak ada teks untuk disalin', 'error')
        return
    }
    
    try {
        if (navigator.clipboard && window.isSecureContext) {
            await navigator.clipboard.writeText(text)
        } else {
            // Fallback for older browsers or non-HTTPS
            const textArea = document.createElement('textarea')
            textArea.value = text
            textArea.style.position = 'fixed'
            textArea.style.left = '-999999px'
            textArea.style.top = '-999999px'
            document.body.appendChild(textArea)
            textArea.focus()
            textArea.select()
            
            const successful = document.execCommand('copy')
            document.body.removeChild(textArea)
            
            if (!successful) {
                throw new Error('Fallback copy failed')
            }
        }
        
        const messages = {
            va: 'Virtual Account berhasil disalin!',
            biller: 'Kode Biller berhasil disalin!',
            default: 'Berhasil disalin!'
        }
        
        showNotification(messages[type] || messages.default, 'success')
        
    } catch (err) {
        showNotification('Gagal menyalin! Silakan salin manual.', 'error')
    }
}

const checkPaymentStatus = async () => {
    if (!props.order) {
        showNotification('Data pesanan tidak tersedia', 'error')
        return
    }
    
    checkingStatus.value = true
    
    try {
        await new Promise(resolve => setTimeout(resolve, 1000))
        
        router.reload({
            onSuccess: (page) => {
                if (page.props.order && page.props.order.payment_status === 'paid') {
                    if (statusPollingInterval.value) {
                        clearInterval(statusPollingInterval.value)
                        statusPollingInterval.value = null
                    }
                    handleSuccessfulPayment(page.props.order)
                } else {
                    showNotification('Status pembayaran telah diperbarui', 'success')
                }
            },
            onError: () => {
                showNotification('Gagal memperbarui status pembayaran', 'error')
            },
            onFinish: () => {
                checkingStatus.value = false
            }
        })
    } catch (error) {
        showNotification('Terjadi kesalahan saat mengecek status', 'error')
        checkingStatus.value = false
    }
}

const generateQRCode = async () => {
    if (!props.qrString) {
        return
    }
    
    await nextTick()
    
    if (!qrCodeContainer.value) {
        // Retry after DOM is ready with max retries
        let retries = 0
        const maxRetries = 10
        
        const checkAndRetry = () => {
            if (retries >= maxRetries) {
                console.warn('QR Code container not found after max retries')
                return
            }
            
            retries++
            if (qrCodeContainer.value && document.contains(qrCodeContainer.value)) {
                generateQRCode()
            } else {
                setTimeout(checkAndRetry, 100)
            }
        }
        
        setTimeout(checkAndRetry, 100)
        return
    }
    
    if (!document.contains(qrCodeContainer.value)) {
        return
    }
    
    if (qrCodeGenerated.value) {
        return
    }
    
    try {
        qrCodeLoading.value = true
        
        if (qrCodeContainer.value) {
            qrCodeContainer.value.innerHTML = ''
            
            qrCodeContainer.value.innerHTML = `
                <div class="flex items-center justify-center h-full">
                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                </div>
            `
        }
        
        const containerSize = qrCodeContainer.value?.offsetWidth || 160
        const qrSize = Math.min(containerSize, 192)
        
        const canvas = document.createElement('canvas')
        await QRCode.toCanvas(canvas, props.qrString, {
            width: qrSize,
            height: qrSize,
            margin: 2,
            color: {
                dark: '#000000',
                light: '#FFFFFF'
            }
        })
        
        if (qrCodeContainer.value && document.contains(qrCodeContainer.value)) {
            qrCodeContainer.value.innerHTML = ''
            qrCodeContainer.value.appendChild(canvas)
        }
        
        qrCodeGenerated.value = true
        qrCodeLoading.value = false
        
    } catch (error) {
        qrCodeLoading.value = false
        
        if (qrCodeContainer.value && document.contains(qrCodeContainer.value)) {
            showNotification('Gagal membuat QR Code: ' + error.message, 'error')
            
            // Fallback: show QR string as text
            qrCodeContainer.value.innerHTML = `
                <div class="text-center p-4 bg-gray-100 dark:bg-gray-700 rounded">
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">QR Code tidak dapat dimuat</p>
                    <p class="text-xs font-mono break-all text-gray-800 dark:text-gray-200">${props.qrString}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-500 mt-2">Salin kode di atas untuk pembayaran manual</p>
                </div>
            `
        }
    }
}

onMounted(async () => {
    try {
        const initializeComponent = async () => {
            if (!props.order) {
                // If order is not available, wait a bit and retry (max 5 times)
                let retries = 0
                const maxRetries = 5
                
                const checkProps = () => {
                    if (props.order || retries >= maxRetries) {
                        if (props.order) {
                            initializeComponent()
                        }
                        return
                    }
                    retries++
                    setTimeout(checkProps, 100)
                }
                
                setTimeout(checkProps, 100)
                return
            }
            
            if (props.order.payment_status === 'paid') {
                isRedirecting.value = true
                showNotification('Pembayaran berhasil! Halaman dialihkan...', 'success')
                setTimeout(() => {
                    window.location.href = `/invoice/${props.order.order_number}`
                }, 1000)
                return
            }

            await nextTick()
            await new Promise(resolve => setTimeout(resolve, 50))
            
            startCountdown()
            
            startAutoReload()
            
            if (props.qrString) {
                setTimeout(() => {
                    if (props.qrString && qrCodeContainer.value) {
                        generateQRCode()
                    }
                }, 200)
            }
            
            if (props.order && (props.order.payment_status === 'pending' || props.order.status === 'pending')) {
                setupPaymentStatusListener()
            }
        }
        
        await initializeComponent()
    } catch (error) {
        console.error('Error in onMounted:', error)
        // Silent error handling for production
    }
})

const setupPaymentStatusListener = () => {
    window.addEventListener('payment-status-updated', handlePaymentStatusUpdate)
    
    setTimeout(() => {
        checkPaymentStatusOnce()
    }, 2000)
    
    startCallbackListener()
}

const handlePaymentStatusUpdate = (event) => {
    if (!props.order) return
    
    const { order_number, payment_status, should_redirect } = event.detail
    
    if (order_number === props.order.order_number) {
        if (payment_status === 'paid' || should_redirect) {
            handleSuccessfulPayment({ order_number, payment_status })
        } else {
            // Force full page reload for other status changes
            window.location.reload()
        }
    }
}

const checkPaymentStatusOnce = async () => {
    if (checkingStatus.value || !props.order) return
    
    try {
        checkingStatus.value = true
        
        const callbackResponse = await fetch(`/payment/${props.order.order_number}/callback-status`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        
        if (callbackResponse.ok) {
            const callbackData = await callbackResponse.json()
            
            if (callbackData.has_callback) {
                if (callbackData.should_redirect) {
                    handleSuccessfulPayment({ 
                        order_number: props.order.order_number,
                        payment_status: callbackData.payment_status 
                    })
                } else {
                    // Force full page reload for other status changes
                    window.location.reload()
                }
                return
            }
            
            if (callbackData.payment_status === 'paid') {
                handleSuccessfulPayment({ 
                    order_number: props.order.order_number,
                    payment_status: callbackData.payment_status 
                })
                return
            }
        }
    } catch (error) {
        // Silent error handling
    } finally {
        checkingStatus.value = false
    }
}

const startCallbackListener = () => {
    if (!props.order) return
    
    let checkCount = 0
    const maxFastChecks = 8 // Check every 5 seconds for first 8 times (40 seconds)
    
    const performCallbackCheck = async () => {
        if (!props.order) {
            if (callbackCheckInterval) {
                clearInterval(callbackCheckInterval)
            }
            return
        }
        
        try {
            const response = await fetch(`/payment/${props.order.order_number}/callback-status`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            
            if (response.ok) {
                const data = await response.json()
                
                if (data.payment_status === 'paid' || data.should_redirect) {
                    clearInterval(callbackCheckInterval)
                    handleSuccessfulPayment({ 
                        order_number: props.order.order_number,
                        payment_status: data.payment_status 
                    })
                    return
                }
                
                if (data.has_callback) {
                    clearInterval(callbackCheckInterval)
                    
                    if (data.should_redirect) {
                        handleSuccessfulPayment({ 
                            order_number: props.order.order_number,
                            payment_status: data.payment_status 
                        })
                    } else {
                        // Force full page reload for other status changes
                        showNotification('Status pembayaran telah diperbarui', 'info')
                        setTimeout(() => {
                            window.location.reload()
                        }, 1000)
                    }
                }
            }
        } catch (error) {
            // Silent error handling
        }
        
        checkCount++
    }
    
    // Start with frequent checks, then reduce frequency
    const callbackCheckInterval = setInterval(async () => {
        await performCallbackCheck()
        
        // After initial fast checks, reduce frequency
        if (checkCount >= maxFastChecks) {
            clearInterval(callbackCheckInterval)
            statusPollingInterval.value = setInterval(performCallbackCheck, 30000)
        }
    }, 5000) // Start with every 5 seconds
    
    statusPollingInterval.value = callbackCheckInterval
}

const handleSuccessfulPayment = (order) => {
    isRedirecting.value = true
    showNotification('Pembayaran berhasil! Halaman dialihkan...', 'success')
    
    // Force full page reload then redirect
    setTimeout(() => {
        window.location.href = `/invoice/${order.order_number}`
    }, 1500)
}

onUnmounted(() => {
    try {
        window.removeEventListener('payment-status-updated', handlePaymentStatusUpdate)
        
        if (statusPollingInterval.value) {
            clearInterval(statusPollingInterval.value)
            statusPollingInterval.value = null
        }
        
        if (countdownInterval.value) {
            clearInterval(countdownInterval.value)
            countdownInterval.value = null
        }
        
        if (autoReloadInterval.value) {
            clearInterval(autoReloadInterval.value)
            autoReloadInterval.value = null
        }
        
        if (qrCodeContainer.value) {
            qrCodeContainer.value.innerHTML = ''
        }
        notifications.value = []
    } catch (error) {
        // Silent error handling
    }
})

const hasAnyInstructions = computed(() => {
    if (!props.paymentInstructions) return false
    
    return (
        (props.paymentInstructions.atm && props.paymentInstructions.atm.length > 0) ||
        (props.paymentInstructions.internet_banking && props.paymentInstructions.internet_banking.length > 0) ||
        (props.paymentInstructions.mobile_banking && props.paymentInstructions.mobile_banking.length > 0) ||
        (props.paymentInstructions.sms_banking && props.paymentInstructions.sms_banking.length > 0) ||
        props.qrString
    )
})

// Calculate platform fee (if total is different from subtotal + payment fee)
const calculatePlatformFee = () => {
    if (!props.order) return 0
    
    const subtotal = parseFloat(props.order.subtotal || props.order.total_amount || 0)
    const paymentFee = parseFloat(props.order.fee_amount || 0)
    const total = parseFloat(props.order.total || props.order.total_amount || 0)
    
    const calculatedTotal = subtotal + paymentFee
    const platformFee = total - calculatedTotal
    
    // Only show platform fee if it's positive and significant (> 0.01)
    return platformFee > 0.01 ? platformFee : 0
}
const getStatusColor = (status) => {
    const colors = {
        'pending': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
        'paid': 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
        'failed': 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
        'cancelled': 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400',
        'expired': 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'
    }
    return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
}

const getStatusText = (status) => {
    const texts = {
        'pending': 'Menunggu Pembayaran',
        'unpaid': 'Menunggu Pembayaran',
        'paid': 'Sudah Dibayar',
        'failed': 'Pembayaran Gagal',
        'cancelled': 'Pembayaran Dibatalkan',
        'expired': 'Pembayaran Kedaluwarsa'
    }
    return texts[status] || 'Status Tidak Dikenal'
}

const formatPrice = (price) => {
    let numericPrice = 0
    
    if (typeof price === 'string') {
        const cleanPrice = price.replace(/[^\d.-]/g, '')
        numericPrice = parseFloat(cleanPrice) || 0
    } else if (typeof price === 'number') {
        numericPrice = price
    } else {
        numericPrice = 0
    }
    
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

const formatExpiry = (date) => {
    const d = new Date(date)
    const day = String(d.getDate()).padStart(2, '0')
    const month = d.toLocaleDateString('en-US', { month: 'short' })
    const year = d.getFullYear()
    const hours = String(d.getHours()).padStart(2, '0')
    const minutes = String(d.getMinutes()).padStart(2, '0')
    return `${day} ${month} ${year} ${hours}:${minutes}`
}

const updateCountdown = () => {
    if (!props.order || !props.order.expires_at) {
        countdownText.value = ''
        return
    }
    
    try {
        const now = new Date().getTime()
        const expiryDate = props.order.expires_at instanceof Date 
            ? props.order.expires_at 
            : new Date(props.order.expires_at)
        const expiry = expiryDate.getTime()
        
        if (isNaN(expiry)) {
            countdownText.value = 'Waktu tidak valid'
            return
        }
        
        const difference = expiry - now
        
        if (difference <= 0) {
            isExpired.value = true
            countdownText.value = 'Waktu habis'
            if (countdownInterval.value) {
                clearInterval(countdownInterval.value)
                countdownInterval.value = null
            }
            return
        }
        
        isExpired.value = false
        
        const days = Math.floor(difference / (1000 * 60 * 60 * 24))
        const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
        const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60))
        const seconds = Math.floor((difference % (1000 * 60)) / 1000)
        
        if (days > 0) {
            countdownText.value = `${days} hari ${hours}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
        } else if (hours > 0) {
            countdownText.value = `${hours}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
        } else {
            countdownText.value = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`
        }
    } catch (error) {
        countdownText.value = 'Error menghitung waktu'
    }
}

const startCountdown = () => {
    if (!props.order || !props.order.expires_at) return
    
    updateCountdown()
    
    if (countdownInterval.value) {
        clearInterval(countdownInterval.value)
    }
    
    countdownInterval.value = setInterval(() => {
        updateCountdown()
    }, 1000)
}

const startAutoReload = () => {
    if (!props.order) return
    
    if (props.order.payment_status === 'paid' || props.order.status === 'expired') {
        return
    }
    
    if (autoReloadInterval.value) {
        clearInterval(autoReloadInterval.value)
    }
    
    autoReloadInterval.value = setInterval(() => {
        if (!props.order || props.order.payment_status === 'paid' || props.order.status === 'expired') {
            if (autoReloadInterval.value) {
                clearInterval(autoReloadInterval.value)
                autoReloadInterval.value = null
            }
            return
        }
        
        window.location.reload()
    }, 30000) // 30 seconds
}

const getProductUrl = () => {
    if (props.order && props.order.items && props.order.items.length > 0) {
        const firstItem = props.order.items[0]
        if (firstItem.product && firstItem.product.slug) {
            return `/product/${firstItem.product.slug}`
        }
        // Fallback: if product relation is not available, return products list
    }
    return '/products'
}
</script>