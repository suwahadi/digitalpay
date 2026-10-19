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
                        <p class="font-medium">{{ message }}</p>
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
                        Reset Password
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        Masukkan password baru untuk akun Anda
                    </p>
                </div>

                <!-- Reset Password Form -->
                <form @submit.prevent="submit" class="mt-8 space-y-6">
                    <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
                        <!-- Password Input -->
                        <div class="mb-6">
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Password Baru
                            </label>
                            <div class="relative">
                                <input
                                    id="password"
                                    v-model="form.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    class="w-full px-4 py-3 pr-12 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors"
                                    placeholder="Masukkan password baru"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': errors.password }"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                >
                                    <svg v-if="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                                    </svg>
                                </button>
                            </div>
                            <p v-if="errors.password" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ errors.password }}
                            </p>
                        </div>

                        <!-- Password Confirmation Input -->
                        <div class="mb-6">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Konfirmasi Password Baru
                            </label>
                            <div class="relative">
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    :type="showPasswordConfirmation ? 'text' : 'password'"
                                    required
                                    class="w-full px-4 py-3 pr-12 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors"
                                    placeholder="Konfirmasi password baru"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': errors.password_confirmation }"
                                />
                                <button
                                    type="button"
                                    @click="showPasswordConfirmation = !showPasswordConfirmation"
                                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                                >
                                    <svg v-if="!showPasswordConfirmation" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <svg v-else class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21" />
                                    </svg>
                                </button>
                            </div>
                            <p v-if="errors.password_confirmation" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ errors.password_confirmation }}
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
                            {{ processing ? 'Mereset...' : 'Reset Password' }}
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
                                    Reset Password Gagal
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

// Props
const props = defineProps({
    token: String,
    email: String,
})

// Page data
const { props: pageProps } = usePage()
const message = computed(() => {
    const flashMessage = pageProps.flash?.message
    return flashMessage && flashMessage.trim() ? flashMessage : 'Password berhasil direset! Silakan login dengan password baru Anda.'
})

// Toast notifications
const showSuccessToast = ref(false)

const showPassword = ref(false)
const showPasswordConfirmation = ref(false)
const processing = ref(false)

const form = reactive({
    token: props.token || '',
    email: props.email || '',
    password: '',
    password_confirmation: '',
})

const errors = ref({})

// Auto-hide toast notifications
const showToast = (toastRef, duration = 4000) => {
    toastRef.value = true
    setTimeout(() => {
        toastRef.value = false
    }, duration)
}

const submit = () => {
    processing.value = true
    errors.value = {}

    router.post('/reset-password', form, {
        onSuccess: (page) => {
            processing.value = false
            // Show toast - message will be shown via watch or default text
            showToast(showSuccessToast, 5000)
            // Redirect to login after 2 seconds
            setTimeout(() => {
                router.visit('/login')
            }, 2000)
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

// Watch for success message and show toast
watch(message, (newMessage) => {
    if (newMessage) {
        showToast(showSuccessToast, 5000)
    }
}, { immediate: true })

// Show success message toast if present on mount
onMounted(() => {
    if (message.value) {
        showToast(showSuccessToast, 5000)
    }
})
</script>
