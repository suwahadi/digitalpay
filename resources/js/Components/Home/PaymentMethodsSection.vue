<template>
    <section class="py-16 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    Metode Pembayaran
                </h2>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
                <div 
                    v-for="method in paymentMethods" 
                    :key="method.id"
                    class="bg-white dark:bg-gray-800 rounded-xl p-6 text-center shadow-sm hover:shadow-md transition-all duration-300 group hover:scale-105"
                >
                    <div class="w-16 h-16 mx-auto mb-3 rounded-lg bg-gray-50 dark:bg-gray-700 flex items-center justify-center group-hover:bg-blue-50 dark:group-hover:bg-blue-900/20 transition-colors">
                        <img
                            :src="method.logo"
                            :alt="method.name"
                            class="max-w-12 max-h-12 object-contain"
                        />
                    </div>
                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm">
                        {{ method.name }}
                    </h3>
                </div>
            </div>

        </div>
    </section>
</template>

<script setup>
defineProps({
    paymentMethods: {
        type: Array,
        default: () => [
            {
                id: 1,
                name: 'GoPay',
                type: 'E-Wallet',
                logo: '/storage/images/payment/gopay.png'
            },
            {
                id: 2,
                name: 'OVO',
                type: 'E-Wallet',
                logo: '/storage/images/payment/ovo.png'
            },
            {
                id: 3,
                name: 'DANA',
                type: 'E-Wallet',
                logo: '/storage/images/payment/dana.png'
            },
            {
                id: 4,
                name: 'ShopeePay',
                type: 'E-Wallet',
                logo: '/storage/images/payment/shopeepay.png'
            },
            {
                id: 5,
                name: 'BCA',
                type: 'Bank',
                logo: '/storage/images/payment/bca.png'
            },
            {
                id: 6,
                name: 'Mandiri',
                type: 'Bank',
                logo: '/storage/images/payment/mandiri.png'
            }
        ]
    }
})
</script>