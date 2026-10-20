<template>
    <AppLayout>

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
import { ref, reactive, computed, nextTick, onMounted, onUnmounted } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    email: String,
    status: String,
    canResendOtp: Boolean,
    remainingSeconds: {
        type: Number,
        default: 0
    }
})

const { props: pageProps } = usePage()
const message = computed(() => pageProps.flash?.message)


const processing = ref(false)
const resendProcessing = ref(false)
const errors = ref({})

const resendCountdown = ref(0)
const resendTimer = ref(null)

const otpDigits = reactive(['', '', '', '', '', ''])
const otpInputs = ref([])

const isOtpComplete = computed(() => {
    return otpDigits.every(digit => digit !== '')
})

const fullOtpCode = computed(() => {
    return otpDigits.join('')
})

const formatTime = (seconds) => {
    const minutes = Math.floor(seconds / 60)
    const remainingSeconds = seconds % 60
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
}

const startResendTimer = (seconds = 600) => {
    resendCountdown.value = seconds
    resendTimer.value = setInterval(() => {
        resendCountdown.value--
        if (resendCountdown.value <= 0) {
            clearInterval(resendTimer.value)
            resendTimer.value = null
            resendCountdown.value = 0
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

const notifyRegistered = () => {
    if (message.value?.includes('Registrasi berhasil')) {
        window.toast?.success(message.value, null, 3000)
    }
}

const handleOtpInput = (index, event) => {
    const value = event.target.value
    
    if (!/^\d*$/.test(value)) {
        event.target.value = otpDigits[index]
        return
    }
    
    otpDigits[index] = value
    
    if (value && index < 5) {
        nextTick(() => {
            otpInputs.value[index + 1]?.focus()
        })
    }
}

const handleKeyDown = (index, event) => {
    if (event.key === 'Backspace' && !otpDigits[index] && index > 0) {
        nextTick(() => {
            otpInputs.value[index - 1]?.focus()
        })
    }
    
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
    
    const nextEmptyIndex = otpDigits.findIndex(digit => digit === '')
    const focusIndex = nextEmptyIndex !== -1 ? nextEmptyIndex : 5
    
    nextTick(() => {
        otpInputs.value[focusIndex]?.focus()
    })
}

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
            
            otpDigits.fill('')
            nextTick(() => {
                otpInputs.value[0]?.focus()
            })
            
            startResendTimer()
            
            window.toast?.success(`Kode OTP telah dikirimkan ke ${props.email}`, null, 4000)
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

onMounted(() => {
    nextTick(() => {
        otpInputs.value[0]?.focus()
    })
    
    if (props.remainingSeconds > 0) {
        startResendTimer(props.remainingSeconds)
    }
    
    notifyRegistered()
})

onUnmounted(() => {
    clearResendTimer()
})
</script>