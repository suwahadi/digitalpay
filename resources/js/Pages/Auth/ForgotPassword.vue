<template>
    <AppLayout>
        <!-- Success Notification Toast -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition ease-out duration-300"
                enter-from-class="opacity-0 transform translate-y-2 translate-x-1/2"
                enter-to-class="opacity-100 transform translate-y-0 translate-x-1/2"
                leave-active-class="transition ease-in duration-200"
                leave-from-class="opacity-100 transform translate-y-0 translate-x-1/2"
                leave-to-class="opacity-0 transform translate-y-2 translate-x-1/2"
            >
                <div
                    v-if="showSuccessToast"
                    class="fixed top-4 right-4 z-50 max-w-sm w-full bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3"
                >
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <p class="font-medium">{{ toastMessage || message }}</p>
                    </div>
                    <button
                        @click="showSuccessToast = false"
                        class="text-white hover:text-gray-200 transition-colors"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </Transition>
        </Teleport>

        <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full space-y-8">
                <!-- Header -->
                <div class="text-center">
                    <h2 class="mt-6 text-3xl font-bold text-gray-900 dark:text-white">
                        Lupa Password
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Masukkan email Anda untuk menerima link reset password
                    </p>
                </div>

                <!-- Forgot Password Form -->
                <form @submit.prevent="submit" class="mt-8 space-y-6">
                    <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
                        <!-- Email Input -->
                        <div class="mb-6">
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Email
                            </label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors"
                                placeholder="Masukkan email Anda"
                                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': errors.email }"
                            />
                            <p v-if="errors.email" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ errors.email }}
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            :disabled="processing"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        >
                            <svg v-if="processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ processing ? 'Mengirim...' : 'Kirim Link Reset Password' }}
                        </button>
                    </div>

                    <!-- Error Message -->
                    <div v-if="errors.general" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-200">
                                    Terjadi Kesalahan
                                </h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-300">
                                    {{ errors.general }}
                                </div>
                            </div>
                        </div>
                    </div>

                </form>

                <!-- Back to Login Link -->
                <div class="text-center">
                    <Link href="/login" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                        ← Kembali ke Login
                    </Link>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const { props: pageProps } = usePage()
const message = computed(() => pageProps.flash?.message)

const showSuccessToast = ref(false)
const toastMessage = ref('')

const processing = ref(false)

const form = reactive({
    email: '',
})

const errors = ref({ ...(pageProps.errors || {}) })

const showToast = (toastRef, duration = 4000) => {
    toastRef.value = true
    setTimeout(() => {
        toastRef.value = false
    }, duration)
}

const submit = () => {
    processing.value = true
    errors.value = {}

    router.post('/forgot-password', form, {
        onSuccess: (page) => {
            processing.value = false
            toastMessage.value = page.props.flash?.message || 'Jika email tersebut terdaftar, link reset password telah dikirim ke email Anda.'
            showToast(showSuccessToast, 5000)
        },
        onError: (serverErrors) => {
            processing.value = false
            errors.value = serverErrors
        },
        onFinish: () => {
            processing.value = false
        }
    })
}

watch(message, (newMessage) => {
    if (newMessage) {
        toastMessage.value = newMessage
        showToast(showSuccessToast, 5000)
    }
}, { immediate: true })

onMounted(() => {
    if (message.value) {
        toastMessage.value = message.value
        showToast(showSuccessToast, 5000)
    }
})
</script>
