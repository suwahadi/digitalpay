<template>
    <AppLayout>
        <Head title="Halaman tidak ditemukan" />

        <div class="flex justify-center px-4 pt-24 pb-16 sm:min-h-screen sm:items-center sm:py-12">
            <div class="w-full max-w-lg text-center">
                <p class="text-6xl font-bold text-blue-600 dark:text-blue-400">404</p>
                <h1 class="mt-4 text-2xl font-bold text-gray-900 dark:text-white">
                    Halaman tidak ditemukan
                </h1>
                <p class="mt-3 text-gray-600 dark:text-gray-400">
                    {{ message || 'Halaman yang Anda cari tidak ada atau sudah dipindahkan.' }}
                </p>
                <div class="mt-8 grid grid-cols-2 gap-3">
                    <Link href="/" class="px-4 py-3 rounded-xl border-2 border-blue-600 bg-blue-600 text-center text-white font-medium hover:bg-blue-700 hover:border-blue-700 transition-colors">
                        Halaman Utama
                    </Link>
                    <Link href="/products" class="px-4 py-3 rounded-xl border-2 border-blue-600 bg-white text-center font-medium text-blue-600 hover:bg-blue-50 dark:border-blue-400 dark:bg-transparent dark:text-blue-400 dark:hover:bg-blue-400/10 transition-colors">
                        Lihat Produk
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
    message: { type: String, default: '' },
})
</script>
