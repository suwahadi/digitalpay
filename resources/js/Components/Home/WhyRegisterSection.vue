<template>
    <section class="py-16 bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-800 dark:to-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    Yuk Daftar Member {{ $page.props.settings?.title || 'DigitalPay' }}! 
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400">
                    Dapatkan keuntungan eksklusif dengan menjadi member {{ $page.props.settings?.title || 'DigitalPay' }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div 
                    v-for="benefit in benefits" 
                    :key="benefit.id"
                    class="text-center group"
                >
                    <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-300">
                        <IconifyIcon :icon="benefit.icon" width="40" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3">
                        {{ benefit.title }}
                    </h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        {{ benefit.description }}
                    </p>
                </div>
            </div>

            <div class="text-center mt-12">
                <Link
                    v-if="!$page.props.auth.user"
                    href="/register"
                    class="btn btn-primary btn-lg"
                >
                    Daftar Sekarang
                </Link>
            </div>
        </div>
    </section>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const benefits = [
    {
        id: 1,
        icon: 'lucide:gift',
        title: 'Cashback & Bonus',
        description: 'Dapatkan cashback hingga 5% dan bonus poin setiap transaksi'
    },
    {
        id: 2,
        icon: 'lucide:clock',
        title: 'Transaksi 24/7',
        description: 'Akses layanan kapan saja, di mana saja tanpa batas waktu'
    },
    {
        id: 3,
        icon: 'lucide:shield-check',
        title: 'Keamanan Terjamin',
        description: 'Sistem keamanan berlapis untuk melindungi setiap transaksi Anda'
    }
]
</script>