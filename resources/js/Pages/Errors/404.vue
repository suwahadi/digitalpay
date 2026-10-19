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
                <div class="mt-8 flex flex-col sm:flex-row justify-center gap-3">
                    <Link href="/" class="px-6 py-3 rounded-xl bg-blue-600 text-white font-medium hover:bg-blue-700 transition-colors">
                        Kembali ke Beranda
                    </Link>
                    <Link href="/products" class="px-6 py-3 rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 font-medium hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
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
