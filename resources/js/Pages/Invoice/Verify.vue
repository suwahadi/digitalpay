<template>
    <AppLayout>
        <Head :title="`Verifikasi Pesanan ${orderNumber}`" />

        <div class="flex justify-center px-4 pt-24 pb-10 sm:min-h-screen sm:items-center sm:py-12 sm:px-6 lg:px-8">
            <div class="max-w-md w-full space-y-6 sm:space-y-8">
                <div class="text-center">
                    <h1 class="text-2xl sm:mt-6 sm:text-3xl font-bold text-gray-900 dark:text-white">
                        Verifikasi Pesanan
                    </h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Untuk membuka pesanan <span class="font-semibold text-gray-900 dark:text-white whitespace-nowrap">{{ orderNumber }}</span>,
                        masukkan email yang Anda pakai saat checkout.
                    </p>
                </div>

                <form @submit.prevent="submit" class="bg-white dark:bg-gray-800 p-6 sm:p-8 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Email pembeli
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autofocus
                        autocomplete="email"
                        placeholder="email@contoh.com"
                        :aria-invalid="!!form.errors.email"
                        aria-describedby="email-error"
                        class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors"
                        :class="{ 'border-red-500 dark:border-red-500 focus:ring-red-500 focus:border-red-500': form.errors.email }"
                    />
                    <p v-if="form.errors.email" id="email-error" class="mt-2 text-sm text-red-600 dark:text-red-400">
                        {{ form.errors.email }}
                    </p>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="mt-6 w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                    >
                        {{ form.processing ? 'Memeriksa...' : 'Buka Pesanan' }}
                    </button>
                </form>

                <p class="text-center text-sm text-gray-500 dark:text-gray-400">
                    Lupa email yang dipakai?
                    <a
                        :href="`https://wa.me/${$page.props.settings?.contact?.whatsapp || '6281234567890'}`"
                        target="_blank"
                        rel="noopener"
                        class="font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400"
                    >Hubungi admin via WhatsApp</a>
                </p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    orderNumber: { type: String, required: true },
})

const form = useForm({ email: '' })

const submit = () => {
    form.post(`/order/${encodeURIComponent(props.orderNumber)}/verify`, {
        preserveScroll: true,
    })
}
</script>
