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

        <!-- OTP Resend Notification Toast -->
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
                    v-if="showResendToast"
                    class="fixed top-4 right-4 z-50 max-w-sm w-full bg-blue-500 text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3"
                >
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <div class="flex-1">
                        <p class="font-medium">Kode OTP telah dikirimkan ke {{ email }}</p>
                    </div>
                    <button
                        @click="showResendToast = false"
                        class="text-white hover:text-gray-200 transition-colors"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </Transition>
        </Teleport>

        <div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-md w-full">
                <!-- Success State -->
                <div v-if="status === 'success'" class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-8">
                    <div class="text-center">
                        <!-- Success Icon -->
                        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 dark:bg-green-900/20 mb-6">
                            <svg class="h-10 w-10 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                        
                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-4">
                            Verifikasi Berhasil! 🎉
                        </h2>
                        <p class="text-gray-600 dark:text-gray-400 mb-8 text-sm sm:text-base leading-relaxed">
                            Email Anda telah berhasil diverifikasi.<br>
                            Akun Anda sekarang sudah aktif dan siap digunakan.
                        </p>
                        
                        <div class="space-y-3">
                            <Link 
                                href="/login" 
                                class="w-full flex justify-center py-3 px-6 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200"
                            >
                                Login Sekarang
                            </Link>
                            
                            <Link 
                                href="/" 
                                class="w-full flex justify-center py-3 px-6 border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200"
                            >
                                Kembali ke Beranda
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Verification Form -->
                <div v-else class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 sm:p-8 mt-20">
                    <!-- Header -->
                    <div class="text-center mb-8">
                        
                        <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 dark:text-white mb-3">
                            Verifikasi Email
                        </h2>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                            Masukkan kode OTP yang telah dikirim ke email Anda
                        </p>
                    </div>

                    <!-- Verification Form -->
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- OTP Input -->
                        <div>
                            <label for="otp_code" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4 text-center">
                                Kode Verifikasi (6 Digit)
                            </label>
                            
                            <!-- OTP Input Fields -->
                            <div class="flex justify-center space-x-2 sm:space-x-3 mb-4">
                                <input
                                    v-for="(digit, index) in otpDigits"
                                    :key="index"
                                    :ref="el => otpInputs[index] = el"
                                    v-model="otpDigits[index]"
                                    type="text"
                                    maxlength="1"
                                    pattern="[0-9]"
                                    class="w-10 h-10 sm:w-12 sm:h-12 text-center text-lg sm:text-xl font-bold border border-gray-300 dark:border-gray-600 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-all duration-200"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': errors.otp_code }"
                                    @input="handleOtpInput(index, $event)"
                                    @keydown="handleKeyDown(index, $event)"
                                    @paste="handlePaste($event)"
                                />
                            </div>
                            
                            <p v-if="errors.otp_code" class="mt-2 text-sm text-red-600 dark:text-red-400 text-center">
                                {{ errors.otp_code }}
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <button
                            type="submit"
                            :disabled="processing || !isOtpComplete"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                        >
                            <svg v-if="processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ processing ? 'Memverifikasi...' : 'Verifikasi Sekarang' }}
                        </button>

                        <!-- Error Message -->
                        <div v-if="errors.general" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                            <div class="flex">
                                <div class="ml-3">
                                    <div class="mt-1 text-sm text-red-700 dark:text-red-300">
                                        {{ errors.general }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- Resend OTP Section -->
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <div class="text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                Tidak menerima kode OTP?
                            </p>
                            
                            <!-- Resend Button (Available) -->
                            <div v-if="resendCountdown === 0">
                                <button
                                    @click="resendOtp"
                                    :disabled="resendProcessing"
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
                                >
                                    <svg v-if="resendProcessing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg v-else class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    {{ resendProcessing ? 'Mengirim ulang...' : 'Kirim Ulang Kode' }}
                                </button>
                            </div>
                            
                            <!-- Countdown Timer -->
                            <div v-else class="text-center">
                                <button
                                    disabled
                                    class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-lg text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 cursor-not-allowed transition-all duration-200"
                                >
                                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Kirim Ulang dalam {{ formatTime(resendCountdown) }}
                                </button>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    Anda dapat meminta kode baru setelah waktu habis
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Instructions -->
                    <div class="mt-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <ul class="text-sm text-blue-700 dark:text-blue-300 space-y-1">
                                    <li>Periksa folder inbox dan spam di email Anda</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Back Link -->
                    <div class="text-center mt-6">
                        <Link href="/register" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300 transition-colors duration-200">
                            ← Kembali ke Registrasi
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive, computed, nextTick, onMounted, onUnmounted, watch } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

// Props from server
const props = defineProps({
    email: String,
    status: String,
    canResendOtp: Boolean,
    remainingSeconds: {
        type: Number,
        default: 0
    }
})

// Page data
const { props: pageProps } = usePage()
const message = computed(() => pageProps.flash?.message)

// Toast notifications
const showSuccessToast = ref(false)
const showResendToast = ref(false)

// Form state
const processing = ref(false)
const resendProcessing = ref(false)
const errors = ref({})

// Resend OTP Timer (10 minutes = 600 seconds)
const resendCountdown = ref(0)
const resendTimer = ref(null)

// OTP handling
const otpDigits = reactive(['', '', '', '', '', ''])
const otpInputs = ref([])

const isOtpComplete = computed(() => {
    return otpDigits.every(digit => digit !== '')
})

const fullOtpCode = computed(() => {
    return otpDigits.join('')
})

// Timer utilities
const formatTime = (seconds) => {
    const minutes = Math.floor(seconds / 60)
    const remainingSeconds = seconds % 60
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
}

const startResendTimer = (seconds = 600) => {
    resendCountdown.value = seconds // Default 10 minutes in seconds
    resendTimer.value = setInterval(() => {
        resendCountdown.value--
        if (resendCountdown.value <= 0) {
            clearInterval(resendTimer.value)
            resendTimer.value = null
            resendCountdown.value = 0 // Ensure it's exactly 0
        }
    }, 1000)
}

const clearResendTimer = () => {
    if (resendTimer.value) {
        clearInterval(resendTimer.value)
        resendTimer.value = null
    }
    resendCountdown.value = 0
}

// Auto-hide toast notifications
const showToast = (toastRef, duration = 3000) => {
    toastRef.value = true
    setTimeout(() => {
        toastRef.value = false
    }, duration)
}

// OTP input handling
const handleOtpInput = (index, event) => {
    const value = event.target.value
    
    // Only allow numbers
    if (!/^\d*$/.test(value)) {
        event.target.value = otpDigits[index]
        return
    }
    
    otpDigits[index] = value
    
    // Move to next input if value is entered
    if (value && index < 5) {
        nextTick(() => {
            otpInputs.value[index + 1]?.focus()
        })
    }
}

const handleKeyDown = (index, event) => {
    // Handle backspace
    if (event.key === 'Backspace' && !otpDigits[index] && index > 0) {
        nextTick(() => {
            otpInputs.value[index - 1]?.focus()
        })
    }
    
    // Handle arrow keys
    if (event.key === 'ArrowLeft' && index > 0) {
        nextTick(() => {
            otpInputs.value[index - 1]?.focus()
        })
    }
    
    if (event.key === 'ArrowRight' && index < 5) {
        nextTick(() => {
            otpInputs.value[index + 1]?.focus()
        })
    }
}

const handlePaste = (event) => {
    event.preventDefault()
    const pasteData = event.clipboardData.getData('text')
    const digits = pasteData.replace(/\D/g, '').slice(0, 6).split('')
    
    digits.forEach((digit, index) => {
        if (index < 6) {
            otpDigits[index] = digit
        }
    })
    
    // Focus on next empty input or last input
    const nextEmptyIndex = otpDigits.findIndex(digit => digit === '')
    const focusIndex = nextEmptyIndex !== -1 ? nextEmptyIndex : 5
    
    nextTick(() => {
        otpInputs.value[focusIndex]?.focus()
    })
}

// Submit verification
const submit = () => {
    if (!isOtpComplete.value) return
    
    processing.value = true
    errors.value = {}

    router.post('/verify', {
        email: props.email,
        otp_code: fullOtpCode.value
    }, {
        onSuccess: () => {
            processing.value = false
            clearResendTimer()
        },
        onError: (serverErrors) => {
            processing.value = false
            errors.value = serverErrors
            
            // Clear OTP on error
            otpDigits.fill('')
            nextTick(() => {
                otpInputs.value[0]?.focus()
            })
        },
        onFinish: () => {
            processing.value = false
        }
    })
}

// Resend OTP
const resendOtp = () => {
    // Validation: prevent spam clicking
    if (resendProcessing.value || resendCountdown.value > 0) {
        return
    }
    
    resendProcessing.value = true
    errors.value = {}

    router.post('/verify/resend', {
        email: props.email
    }, {
        onSuccess: (page) => {
            resendProcessing.value = false
            
            // Clear existing OTP
            otpDigits.fill('')
            nextTick(() => {
                otpInputs.value[0]?.focus()
            })
            
            // Start countdown timer
            startResendTimer()
            
            // Show success toast
            showToast(showResendToast, 4000)
        },
        onError: (serverErrors) => {
            resendProcessing.value = false
            errors.value = serverErrors
        },
        onFinish: () => {
            resendProcessing.value = false
        }
    })
}

// Watch for success message and show toast
watch(message, (newMessage) => {
    if (newMessage && newMessage.includes('Registrasi berhasil')) {
        showToast(showSuccessToast, 3000)
    }
}, { immediate: true })

// Initialize countdown from server data
onMounted(() => {
    nextTick(() => {
        otpInputs.value[0]?.focus()
    })
    
    // Set countdown from server if OTP is still active
    if (props.remainingSeconds > 0) {
        startResendTimer(props.remainingSeconds)
    }
    
    // Show success message toast if present
    if (message.value && message.value.includes('Registrasi berhasil')) {
        showToast(showSuccessToast, 3000)
    }
})

// Cleanup timer on unmount
onUnmounted(() => {
    clearResendTimer()
})
</script>