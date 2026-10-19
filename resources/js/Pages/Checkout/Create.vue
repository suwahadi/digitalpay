<template>
    <AppLayout>
        <Head :title="title" />

        <div v-if="!product || !selectedVariant" class="min-h-screen bg-gray-50 dark:bg-gray-900 pt-20 pb-12 flex items-center justify-center">
            <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <p class="text-gray-600 dark:text-gray-400">Mengalihkan ke halaman utama...</p>
            </div>
        </div>

        <div v-else class="min-h-screen bg-gray-50 dark:bg-gray-900 pt-20 pb-12">
            <div class="w-full mx-auto max-w-[85rem] px-4 py-12 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <nav class="flex mb-4" aria-label="Breadcrumb">
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
                                Checkout
                            </li>
                        </ol>
                    </nav>
                    
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                        Konfirmasi Pemesanan
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-2">
                        Lengkapi data dan pilih metode pembayaran untuk menyelesaikan pesanan Anda
                    </p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    <!-- Main Form -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Customer Information -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 shadow-sm p-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
                                Informasi Pembeli
                            </h2>
                            
                            <form @submit.prevent="submitOrder" class="space-y-4">
                                <div>
                                    <label for="customer_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Nama Lengkap *
                                    </label>
                                    <input
                                        type="text"
                                        id="customer_name"
                                        v-model="form.customer_name"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                        placeholder="Masukkan nama lengkap Anda"
                                    />
                                    <div v-if="errors.customer_name" class="text-red-500 text-sm mt-1">
                                        {{ errors.customer_name }}
                                    </div>
                                </div>

                                <div>
                                    <label for="customer_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Email *
                                    </label>
                                    <input
                                        type="email"
                                        id="customer_email"
                                        v-model="form.customer_email"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                        placeholder="contoh@email.com"
                                    />
                                    <div v-if="errors.customer_email" class="text-red-500 text-sm mt-1">
                                        {{ errors.customer_email }}
                                    </div>
                                </div>

                                <div>
                                    <label for="customer_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Nomor WhatsApp *
                                    </label>
                                    <input
                                        type="tel"
                                        id="customer_phone"
                                        v-model="form.customer_phone"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                        placeholder="08123456789"
                                    />
                                    <div v-if="errors.customer_phone" class="text-red-500 text-sm mt-1">
                                        {{ errors.customer_phone }}
                                    </div>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                        Nomor WhatsApp untuk konfirmasi dan pengiriman produk
                                    </p>
                                </div>

                                <div>
                                    <label for="notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Catatan (Opsional)
                                    </label>
                                    <textarea
                                        id="notes"
                                        v-model="form.notes"
                                        rows="3"
                                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white"
                                        placeholder="Tambahkan catatan atau permintaan khusus..."
                                    ></textarea>
                                    <div v-if="errors.notes" class="text-red-500 text-sm mt-1">
                                        {{ errors.notes }}
                                    </div>
                                </div>
                            </form>
                        </div>


                        <!-- Voucher Modal -->
                        <Teleport to="body">
                            <Transition enter-active-class="duration-300 ease-out" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="duration-200 ease-in" leave-from-class="opacity-100" leave-to-class="opacity-0">
                                <div v-if="showVoucherModal" class="fixed inset-0 z-50 overflow-y-auto">
                                    <div class="flex min-h-screen items-center justify-center p-4">
                                        <div class="fixed inset-0 bg-black/50" @click="closeVoucherModal"></div>
                                        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full p-4 sm:p-6 mx-auto">
                                            <div class="flex items-center justify-between mb-4">
                                                <h3 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white">Voucher Tersedia</h3>
                                                <button @click="closeVoucherModal" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 p-1">
                                                    <IconifyIcon icon="lucide:x" width="20" />
                                                </button>
                                            </div>
                                            <div class="space-y-3 max-h-[60vh] overflow-y-auto pr-1">
                                                <div v-if="allVouchers.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                                                    <IconifyIcon icon="lucide:ticket-x" width="48" class="mx-auto mb-2 opacity-50" />
                                                    <p class="text-sm">Tidak ada voucher tersedia saat ini</p>
                                                </div>
                                                <div v-for="v in allVouchers" :key="v.id" class="border rounded-lg p-3 sm:p-4 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600 transition-colors">
                                                    <div class="font-semibold text-blue-600 dark:text-blue-400 text-sm sm:text-base">{{ v.code }}</div>
                                                    <div class="text-xs sm:text-sm text-gray-600 dark:text-gray-400 mt-1">{{ v.name }}</div>
                                                    <div class="mt-2 text-xs sm:text-sm text-gray-700 dark:text-gray-300 space-y-1">
                                                        <div>
                                                            <span class="text-gray-500">Diskon: </span>
                                                            <span class="font-medium">
                                                                <span v-if="v.discount_type === 'percentage'">{{ v.discount_value }}%<span v-if="v.max_discount && v.max_discount > 0"> (Max: {{ formatPrice(v.max_discount) }})</span></span>
                                                                <span v-else>{{ formatPrice(v.discount_value) }}</span>
                                                            </span>
                                                        </div>
                                                        <div v-if="v.min_total && v.min_total > 0" class="text-xs">
                                                            <span class="text-gray-500">Min. Belanja: </span>
                                                            <span class="font-medium">{{ formatPrice(v.min_total) }}</span>
                                                        </div>
                                                        <div v-if="v.expires_at" class="text-xs text-gray-500">
                                                            Berlaku hingga: {{ formatDate(v.expires_at) }}
                                                        </div>
                                                    </div>
                                                    <div class="mt-3">
                                                        <button 
                                                            @click="applyVoucherFromModal(v)" 
                                                            :disabled="!isVoucherApplicable(v)"
                                                            :class="[
                                                                'w-full px-3 sm:px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                                                                isVoucherApplicable(v) 
                                                                    ? 'bg-blue-600 hover:bg-blue-700 text-white' 
                                                                    : 'bg-gray-200 dark:bg-gray-700 text-gray-400 dark:text-gray-500 cursor-not-allowed'
                                                            ]"
                                                        >
                                                            {{ isVoucherApplicable(v) ? 'Pakai Voucher Ini' : 'Tidak Memenuhi Syarat' }}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </Transition>
                        </Teleport>

                        <!-- Payment Methods -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 shadow-sm p-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
                                Metode Pembayaran
                            </h2>
                            
                            <div v-if="!paymentMethods || paymentMethods.length === 0" class="text-center py-8">
                                <p class="text-gray-500 dark:text-gray-400">
                                    Tidak ada metode pembayaran tersedia.
                                </p>
                            </div>
                            
                            <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4">
                                <button
                                    v-for="method in paymentMethods"
                                    :key="method.id"
                                    type="button"
                                    @click="selectPaymentMethod(method)"
                                    :aria-pressed="form.payment_method_id === method.id"
                                    :class="{
                                        'ring-2 ring-blue-500 bg-blue-50 dark:bg-blue-900/20': form.payment_method_id === method.id,
                                        'border-gray-200 dark:border-gray-700 hover:border-blue-300 dark:hover:border-blue-600': form.payment_method_id !== method.id
                                    }"
                                    class="relative flex flex-col items-center text-center gap-3 border rounded-xl p-4 cursor-pointer transition-all duration-200 bg-white dark:bg-gray-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800"
                                >
                                    <div class="flex h-10 w-full items-center justify-center">
                                        <img
                                            :src="method.logo"
                                            :alt="method.display_name"
                                            class="max-h-9 max-w-full object-contain"
                                            @error="handleImageError"
                                        />
                                    </div>
                                    <div class="w-full">
                                        <h4 class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-white leading-tight line-clamp-2">
                                            {{ method.display_name }}
                                        </h4>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                            {{ getPaymentFeeText(method) }}
                                        </p>
                                    </div>

                                    <div v-if="form.payment_method_id === method.id" class="absolute top-2 right-2">
                                        <div class="w-5 h-5 bg-blue-500 rounded-full flex items-center justify-center">
                                            <IconifyIcon icon="lucide:check" width="12" class="text-white" />
                                        </div>
                                    </div>
                                </button>
                            </div>
                            
                            <div v-if="errors.payment_method_id" class="text-red-500 text-sm mt-4">
                                {{ errors.payment_method_id }}
                            </div>
                        </div>

                        <!-- Voucher Section -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 shadow-sm p-4 sm:p-6">
                            <h2 class="text-lg sm:text-xl font-semibold text-gray-900 dark:text-white mb-4">Gunakan Voucher</h2>
                            <div class="grid grid-cols-[1fr_auto] gap-2 sm:gap-3">
                                <input
                                    type="text"
                                    v-model="voucherCode"
                                    placeholder="Masukkan kode voucher..."
                                    class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white text-sm sm:text-base"
                                />
                                <button
                                    @click="appliedVoucher ? cancelVoucher() : applyVoucher()"
                                    class="px-4 sm:px-5 py-2.5 sm:py-3 rounded-lg text-white bg-gray-900 dark:bg-gray-700 hover:bg-gray-800 flex items-center justify-center gap-1.5 whitespace-nowrap text-sm sm:text-base"
                                >
                                    <template v-if="appliedVoucher">
                                        <IconifyIcon icon="lucide:x" width="16" />
                                        <span>Batalkan</span>
                                    </template>
                                    <template v-else>
                                        <span>Gunakan</span>
                                    </template>
                                </button>
                            </div>
                            <p v-if="voucherChecked && calculateVoucherDiscount() > 0" class="text-sm text-green-600 dark:text-green-400 mt-2">Diskon {{ formatPrice(calculateVoucherDiscount()) }} berhasil diterapkan.</p>
                            <p v-if="voucherChecked && voucherErrorText" class="text-sm text-red-600 dark:text-red-400 mt-2">{{ voucherErrorText }}</p>
                            <div class="mt-3">
                                <button @click="openVoucherModal" class="inline-flex items-center px-3 py-2 text-sm rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-400">
                                    <IconifyIcon icon="lucide:ticket" width="16" class="mr-1.5" />
                                    Cek Promo Tersedia
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="lg:col-span-1">
                        <div class="bg-white dark:bg-gray-800 rounded-xl border dark:border-gray-700 shadow-sm p-6 sticky top-24">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">
                                Ringkasan Pesanan
                            </h2>
                            
                            <!-- Product Details -->
                            <div class="space-y-4 mb-6">
                                <div class="flex space-x-4">
                                    <img 
                                        :src="product.image" 
                                        :alt="product.name"
                                        class="w-16 h-16 object-contain bg-gray-100 dark:bg-gray-700 rounded-lg"
                                        @error="onImageError($event, '/images/placeholder.png')"
                                    />
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm leading-tight">
                                            {{ product.name }}
                                        </h3>
                                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                            {{ product.category.name }}
                                        </p>
                                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                                            {{ selectedVariant.name }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Price Breakdown -->
                                <div class="space-y-3 py-4 border-t border-b dark:border-gray-700">
                                <div v-if="selectedVariant.original_price > selectedVariant.price" class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Harga Normal:</span>
                                    <span class="text-sm text-gray-500 line-through">{{ formatPrice(selectedVariant.original_price) }}</span>
                                </div>

                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">{{ selectedVariant.original_price > selectedVariant.price ? 'Harga Promo:' : 'Harga:' }}</span>
                                    <span class="text-gray-900 dark:text-white">{{ formatPrice(selectedVariant.price) }}</span>
                                </div>

                                <div v-if="calculateVoucherDiscount() > 0" class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Voucher Diskon:</span>
                                    <span class="text-green-600 dark:text-green-400">-{{ formatPrice(calculateVoucherDiscount()) }}</span>
                                </div>
                                
                                <div v-if="selectedPaymentMethod && selectedPaymentMethod.fee_type !== 'none'" class="flex justify-between text-sm">
                                    <span class="text-gray-600 dark:text-gray-400">Fee Platform:</span>
                                    <span class="text-gray-900 dark:text-white">{{ formatPrice(calculatePaymentFee()) }}</span>
                                </div>
                                
                                <div v-if="selectedVariant.original_price && selectedVariant.original_price > selectedVariant.price" class="flex justify-between text-sm text-green-600 dark:text-green-400">
                                    <span>Hemat:</span>
                                    <span>-{{ formatPrice(selectedVariant.original_price - selectedVariant.price) }}</span>
                                </div>
                            </div>
                            
                            <div class="flex justify-between text-lg font-semibold text-gray-900 dark:text-white pt-4">
                                <span>Total:</span>
                                <span class="text-blue-600 dark:text-blue-400">{{ formatPrice(calculateTotal()) }}</span>
                            </div>

                            <!-- Delivery Notice -->
                            <div 
                                v-if="selectedVariant.auto_delivery === true" 
                                class="mt-6 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg"
                            >
                                <div class="flex items-center space-x-2 text-green-600 dark:text-green-400">
                                    <IconifyIcon icon="lucide:zap" width="16" />
                                    <span class="text-sm font-medium">Pengiriman Instan</span>
                                </div>
                                <p class="text-xs text-green-600 dark:text-green-400 mt-1">
                                    Produk akan dikirim otomatis setelah pembayaran dikonfirmasi
                                </p>
                            </div>
                            <div 
                                v-else 
                                class="mt-6 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg"
                            >
                                <div class="flex items-center space-x-2 text-yellow-600 dark:text-yellow-400">
                                    <IconifyIcon icon="lucide:user-check" width="16" />
                                    <span class="text-sm font-medium">Pengiriman Manual</span>
                                </div>
                                <p class="text-xs text-yellow-600 dark:text-yellow-400 mt-1">
                                    Produk akan diproses dan dikirim secara manual oleh Admin
                                </p>
                            </div>

                            <!-- Submit Button -->
                            <button 
                                @click="submitOrder"
                                :disabled="!canSubmit || processing"
                                class="w-full mt-6 bg-blue-600 text-white py-4 px-6 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed font-semibold flex items-center justify-center space-x-2 transition-colors
                                       lg:static lg:rounded-lg lg:shadow-none
                                       sticky -bottom-px rounded-none shadow-lg"
                                style="top: auto;"
                            >
                                <IconifyIcon v-if="processing" icon="lucide:loader-2" width="20" class="animate-spin" />
                                <span>{{ processing ? 'Memproses...' : 'Buat Pesanan' }}</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Confirmation Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="duration-300 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="duration-200 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showConfirmationModal" class="fixed inset-0 z-50 overflow-y-auto">
                    <div class="flex min-h-screen items-center justify-center p-4">
                        <!-- Backdrop -->
                        <div 
                            class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
                            @click="closeConfirmationModal"
                        ></div>
                        
                        <!-- Modal Content -->
                        <Transition
                            enter-active-class="duration-300 ease-out"
                            enter-from-class="opacity-0 scale-95"
                            enter-to-class="opacity-100 scale-100"
                            leave-active-class="duration-200 ease-in"
                            leave-from-class="opacity-100 scale-100"
                            leave-to-class="opacity-0 scale-95"
                        >
                            <div 
                                v-if="showConfirmationModal"
                                class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl max-w-md w-full mx-4 p-6"
                            >
                                <div class="text-center mb-6">
                                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 dark:bg-blue-900 mb-4">
                                        <IconifyIcon icon="lucide:receipt" width="24" class="text-blue-600 dark:text-blue-400" />
                                    </div>
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                        Rincian Pembayaran
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Pastikan data pesanan Anda sudah benar
                                    </p>
                                </div>
                                
                                <!-- Order Details -->
                                <div class="space-y-4 mb-6">
                                    <!-- Subtotal -->
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ formatPrice(selectedVariant.price) }}</span>
                                    </div>
                                    
                                    <!-- Voucher Discount -->
                                    <div v-if="calculateVoucherDiscount() > 0" class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Voucher Diskon:</span>
                                        <span class="font-medium text-green-600 dark:text-green-400">-{{ formatPrice(calculateVoucherDiscount()) }}</span>
                                    </div>
                                    
                                    <!-- Admin Fee -->
                                    <div class="flex justify-between text-sm">
                                        <span class="text-gray-600 dark:text-gray-400">Fee Platform:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ formatPrice(calculatePaymentFee()) }}</span>
                                    </div>
                                    
                                    <!-- Total -->
                                    <div class="flex justify-between text-lg font-semibold pt-2 border-t dark:border-gray-700">
                                        <span class="text-gray-900 dark:text-white">Total Bayar:</span>
                                        <span class="text-blue-600 dark:text-blue-400">{{ formatPrice(calculateTotal()) }}</span>
                                    </div>
                                    
                                    <!-- Payment Method -->
                                    <div class="flex justify-between text-sm pt-2">
                                        <span class="text-gray-600 dark:text-gray-400">Metode Pembayaran:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">{{ selectedPaymentMethod?.name }}</span>
                                    </div>
                                </div>
                                
                                <!-- Contact Information -->
                                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-6">
                                    <h4 class="text-sm font-medium text-gray-900 dark:text-white mb-3">Informasi Kontak:</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Nama:</span>
                                            <span class="text-gray-900 dark:text-white">{{ form.customer_name }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Email:</span>
                                            <span class="text-gray-900 dark:text-white">{{ form.customer_email }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600 dark:text-gray-400">Phone:</span>
                                            <span class="text-gray-900 dark:text-white">{{ form.customer_phone }}</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="flex space-x-3">
                                    <button
                                        @click="closeConfirmationModal"
                                        class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                                    >
                                        Batal
                                    </button>
                                    <button
                                        @click="confirmOrder"
                                        :disabled="processing"
                                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center space-x-2"
                                    >
                                        <IconifyIcon v-if="processing" icon="lucide:loader-2" width="16" class="animate-spin" />
                                        <span>{{ processing ? 'Memproses...' : 'Konfirmasi' }}</span>
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>

<script setup>
import { onImageError } from '@/imageFallback'
import { ref, computed, onMounted, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import IconifyIcon from '@/Components/IconifyIcon.vue'

const props = defineProps({
    title: String,
    product: {
        type: Object,
        required: false,
        default: null
    },
    selectedVariant: {
        type: Object,
        required: false,
        default: null
    },
    quantity: {
        type: Number,
        default: 1
    },
    paymentMethods: {
        type: Array,
        default: () => []
    },
    availableVouchers: {
        type: Array,
        default: () => []
    },
    errors: {
        type: Object,
        default: () => ({})
    },
    auth: {
        type: Object,
        default: () => ({})
    }
})

onMounted(() => {
    if (!props.product || !props.selectedVariant) {
        router.visit('/', {
            data: {
                message: 'Silakan pilih produk terlebih dahulu untuk melanjutkan ke checkout.'
            },
            onBefore: () => {
                console.warn('Checkout data missing, redirecting to home')
            },
            onError: (error) => {
                console.error('Error redirecting:', error)
                // Fallback: use window.location if router fails
                window.location.href = '/'
            }
        })
        return
    }
})

const form = ref({
    product_id: props.product?.id || null,
    variant_id: props.selectedVariant?.id || null,
    quantity: props.quantity || 1,
    customer_name: props.auth?.user?.name || '',
    customer_email: props.auth?.user?.email || '',
    customer_phone: props.auth?.user?.phone || '',
    payment_method_id: null,
    notes: '',
})

const processing = ref(false)
const showConfirmationModal = ref(false)
const voucherCode = ref('')
const appliedVoucher = ref(null)
const showVoucherModal = ref(false)
const voucherChecked = ref(false)
watch(voucherCode, (newVal) => {
  const appliedCode = (appliedVoucher.value?.code || '').trim()
  const currentCode = (String(newVal || '')).trim()
  if (appliedCode !== currentCode) {
    voucherChecked.value = false
    appliedVoucher.value = null
  }
})

const selectedPaymentMethod = computed(() => {
    if (!props.paymentMethods || !form.value.payment_method_id) return null
    return props.paymentMethods.find(method => method.id === form.value.payment_method_id) || null
})

const canSubmit = computed(() => {
    return form.value.customer_name && 
           form.value.customer_email && 
           form.value.customer_phone && 
           form.value.payment_method_id
})

const selectPaymentMethod = (paymentMethod) => {
    if (paymentMethod?.id) {
        form.value.payment_method_id = paymentMethod.id
    }
}

const getPaymentFeeText = (paymentMethod) => {
    if (paymentMethod.fee_type === 'fixed' && paymentMethod.fee_amount > 0) {
        return `+${formatPrice(paymentMethod.fee_amount)}`
    } else if (paymentMethod.fee_type === 'percentage' && paymentMethod.fee_amount > 0) {
        return `+${paymentMethod.fee_amount}%`
    }
    return 'Gratis'
}

const findVoucherByCode = (code) => {
    if (!code) return null
    return props.availableVouchers.find(v => String(v.code).toLowerCase() === String(code).toLowerCase()) || null
}

const calculateVoucherDiscount = () => {
    const subtotal = parseFloat(props.selectedVariant.price || 0)
    const voucher = appliedVoucher.value
    if (!voucher) return 0

    if (voucher.min_total && subtotal < parseFloat(voucher.min_total)) return 0

    if (voucher.discount_type === 'fixed') {
        return Math.min(parseFloat(voucher.discount_value || 0), subtotal)
    }
    const percent = parseFloat(voucher.discount_value || 0)
    let discount = (subtotal * percent) / 100
    const max = parseFloat(voucher.max_discount || 0)
    if (max > 0) discount = Math.min(discount, max)
    return discount
}

const openVoucherModal = () => { showVoucherModal.value = true }
const closeVoucherModal = () => { showVoucherModal.value = false }
const applyVoucher = () => {
    voucherChecked.value = true
    appliedVoucher.value = findVoucherByCode(voucherCode.value)
}

const applyVoucherFromModal = (voucher) => {
    appliedVoucher.value = voucher
    voucherCode.value = voucher.code
    voucherChecked.value = true
    closeVoucherModal()
}
const cancelVoucher = () => {
    appliedVoucher.value = null
    voucherCode.value = ''
    voucherChecked.value = false
}

const allVouchers = computed(() => {
    return props.availableVouchers || []
})

const isVoucherApplicable = (voucher) => {
    if (!voucher) return false
    const subtotal = parseFloat(props.selectedVariant?.price || 0)
    
    if (voucher.min_total && subtotal < parseFloat(voucher.min_total)) {
        return false
    }
    
    if (voucher.expires_at) {
        const exp = new Date(voucher.expires_at)
        if (!isNaN(exp.getTime()) && exp.getTime() < Date.now()) {
            return false
        }
    }
    
    return true
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    const date = new Date(dateString)
    if (isNaN(date.getTime())) return dateString
    return new Intl.DateTimeFormat('id-ID', { 
        day: 'numeric', 
        month: 'short', 
        year: 'numeric' 
    }).format(date)
}

const voucherErrorText = computed(() => {
    const code = (voucherCode.value || '').trim()
    if (!code) return ''
    const subtotal = parseFloat(props.selectedVariant.price || 0)
    const v = appliedVoucher.value || findVoucherByCode(code)
    if (!v) return 'Voucher tidak valid atau sudah tidak berlaku.'
    if (v.min_total && subtotal < parseFloat(v.min_total)) {
        return 'Voucher tidak berlaku: min. belanja ' + formatPrice(v.min_total)
    }
    if (v.expires_at) {
        const exp = new Date(v.expires_at)
        if (!isNaN(exp.getTime()) && exp.getTime() < Date.now()) {
            return 'Voucher kedaluwarsa.'
        }
    }
    const discount = calculateVoucherDiscount()
    if (discount <= 0) return 'Voucher tidak berlaku untuk pesanan ini.'
    return ''
})

const calculatePaymentFee = () => {
    if (!selectedPaymentMethod.value) return 0
    const paymentMethod = selectedPaymentMethod.value
    const subtotal = parseFloat(props.selectedVariant.price || 0)
    const discount = calculateVoucherDiscount()
    const base = Math.max(subtotal - discount, 0)
    if (paymentMethod.fee_type === 'fixed') {
        return parseFloat(paymentMethod.fee_amount || 0)
    } else if (paymentMethod.fee_type === 'percentage') {
        const percentage = parseFloat(paymentMethod.fee_amount || 0)
        return (base * percentage) / 100
    }
    return 0
}

const calculateTotal = () => {
    const subtotal = parseFloat(props.selectedVariant.price || 0)
    const discount = calculateVoucherDiscount()
    const paymentFee = calculatePaymentFee()
    return Math.max(subtotal - discount, 0) + paymentFee
}

const formatPrice = (price) => {
    const numericPrice = parseFloat(price || 0)
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(numericPrice)
}

const submitOrder = () => {
    if (!canSubmit.value || processing.value) return

    showConfirmationModal.value = true
}

const closeConfirmationModal = () => {
    showConfirmationModal.value = false
}

const confirmOrder = () => {
    if (!canSubmit.value || processing.value) return
    
    processing.value = true

    const formData = {
        product_id: props.product.id,
        variant_id: props.selectedVariant.id,
        quantity: 1,
        customer_name: form.value.customer_name,
        customer_email: form.value.customer_email,
        customer_phone: form.value.customer_phone,
        payment_method_id: form.value.payment_method_id,
        notes: form.value.notes || '',
        voucher_code: (appliedVoucher.value?.code || voucherCode.value || '').trim(),
    }
    
    console.log('[Checkout] Submitting order with data:', {
        ...formData,
        payment_method_id: formData.payment_method_id,
        has_csrf_token: !!document.querySelector('meta[name="csrf-token"]'),
    })
    
    router.post('/checkout/store', formData, {
        preserveState: false,
        preserveScroll: false,
        onBefore: () => {
            console.log('[Checkout] Request initiated')
        },
        onStart: () => {
            console.log('[Checkout] Request started')
        },
        onSuccess: (page) => {
            console.log('[Checkout] Request successful', page)
            
            let orderNumber = null
            
            const redirectUrl = page.url || window.location.href
            if (redirectUrl.includes('/invoice/')) {
                return
            }
            if (redirectUrl && redirectUrl.includes('/payment/')) {
                const orderNumberMatch = redirectUrl.match(/\/payment\/([^\/]+)/)
                if (orderNumberMatch) {
                    orderNumber = orderNumberMatch[1]
                    console.log('[Checkout] Order number from URL:', orderNumber)
                }
            }
            
            if (!orderNumber && page.props?.order_number) {
                orderNumber = page.props.order_number
                console.log('[Checkout] Order number from props:', orderNumber)
            }
            
            if (!orderNumber && page.props?.flash?.checkout_order_number) {
                orderNumber = page.props.flash.checkout_order_number
                console.log('[Checkout] Order number from flash:', orderNumber)
            }
            
            if (orderNumber) {
                console.log('[Checkout] Force redirecting to payment page:', orderNumber)
                window.location.href = `/payment/${orderNumber}`
                return
            }
            
            setTimeout(() => {
                console.log('[Checkout] Fallback: checking redirect status')
                const currentPath = window.location.pathname
                
                if (currentPath.includes('/payment/')) {
                    console.log('[Checkout] Successfully redirected to payment page')
                    const match = currentPath.match(/\/payment\/([^\/]+)/)
                    if (match) {
                        console.log('[Checkout] Order number:', match[1])
                    }
                } else if (currentPath.includes('/checkout')) {
                    console.warn('[Checkout] Still on checkout page, forcing reload to trigger redirect')
                    window.location.reload()
                } else {
                    console.warn('[Checkout] Unexpected location:', currentPath)
                    window.location.href = '/'
                }
            }, 300)
        },
        onError: (errors) => {
            console.error('[Checkout] Request failed with errors:', errors)
            processing.value = false
            
            if (errors.csrf || errors._token || errors.message?.includes('419') || errors.message?.includes('CSRF')) {
                alert('Session expired. Please refresh the page and try again.')
                window.location.reload()
                return
            }
            
            if (errors.general) {
                alert('Error: ' + errors.general)
            } else if (errors.payment) {
                alert('Error: ' + errors.payment)
            } else {
                const errorMessages = Object.values(errors).flat()
                alert('Terjadi kesalahan: ' + errorMessages.join(', '))
            }
        },
        onFinish: () => {
            console.log('[Checkout] Request finished')
        }
    })
}

const handleImageError = (event) => {
    onImageError(event, '/images/payment/default.png')
}
</script>