<template>
    <AppLayout>
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white mt-20">
            <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl font-bold">Profil Saya</h1>
                        <p class="text-blue-200">Kelola informasi akun Anda</p>
                    </div>
                    <div class="mt-4 sm:mt-0 flex space-x-3">
                        <Link
                            href="/user"
                            class="inline-flex items-center px-4 py-2 border border-white/20 text-sm font-medium rounded-lg text-white hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>
                            Kembali
                        </Link>
                        <button
                            @click="logout"
                            class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-blue-600 bg-white hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Profile Card -->
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700">
                <div class="p-6">
                    <!-- Avatar Section -->
                    <div class="flex items-center space-x-6 mb-8">
                        <div class="flex-shrink-0">
                            <Avatar 
                                :name="user.name" 
                                :image-url="user.avatar_url" 
                                size="2xl" 
                                :border="true"
                                border-color="border-gray-200 dark:border-gray-600"
                            />
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ user.name }}</h2>
                            <p class="text-gray-600 dark:text-gray-400">{{ user.email }}</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400 mt-2">
                                Terdaftar: {{ formatRegistrationDate(user.created_at) }}
                            </span>
                        </div>
                    </div>

                    <!-- Form Section -->
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Name Input -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nama Lengkap
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors"
                                placeholder="Masukkan nama lengkap"
                                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': errors.name }"
                            />
                            <p v-if="errors.name" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ errors.name }}
                            </p>
                        </div>

                        <!-- Email Input -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Email
                            </label>
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                required
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors"
                                placeholder="Masukkan email"
                                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': errors.email }"
                            />
                            <p v-if="errors.email" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ errors.email }}
                            </p>
                        </div>

                        <!-- Phone Input -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                No. Telepon
                            </label>
                            <input
                                id="phone"
                                v-model="form.phone"
                                type="tel"
                                required
                                class="w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors"
                                placeholder="Masukkan no. telepon"
                                :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': errors.phone }"
                            />
                            <p v-if="errors.phone" class="mt-2 text-sm text-red-600 dark:text-red-400">
                                {{ errors.phone }}
                            </p>
                        </div>

                        <!-- Password Section -->
                        <div class="border-t border-gray-200 dark:border-gray-600 pt-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Ganti Password</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                                Kosongkan field di bawah jika tidak ingin mengganti password
                            </p>
                            
                            <!-- New Password Input -->
                            <div class="mb-4">
                                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Password Baru
                                </label>
                                <div class="relative">
                                    <input
                                        id="password"
                                        v-model="form.password"
                                        :type="showPassword ? 'text' : 'password'"
                                        class="w-full px-4 py-3 pr-12 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors"
                                        placeholder="Masukkan password baru (minimal 6 karakter)"
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

                            <!-- Confirm Password Input -->
                            <div>
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Konfirmasi Password Baru
                                </label>
                                <div class="relative">
                                    <input
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        :type="showPasswordConfirmation ? 'text' : 'password'"
                                        class="w-full px-4 py-3 pr-12 border border-gray-300 dark:border-gray-600 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white transition-colors"
                                        placeholder="Ulangi password baru"
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
                        </div>

                        <!-- Submit Button -->
                        <div class="flex justify-end space-x-4">
                            <button
                                type="button"
                                @click="resetForm"
                                class="px-6 py-3 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-xl text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
                            >
                                Reset
                            </button>
                            <button
                                type="submit"
                                :disabled="processing"
                                class="px-6 py-3 border border-transparent text-sm font-medium rounded-xl text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                            >
                                <svg v-if="processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>


        </div>
    </AppLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import Avatar from '@/Components/Avatar.vue'

const props = defineProps({
    user: Object,
})

const processing = ref(false)
const errors = ref({})
const showPassword = ref(false)
const showPasswordConfirmation = ref(false)

const form = reactive({
    name: props.user.name,
    email: props.user.email,
    phone: props.user.phone,
    password: '',
    password_confirmation: '',
})

const submit = () => {
    processing.value = true
    errors.value = {}

    router.post('/user/profile', form, {
        onSuccess: (page) => {
            processing.value = false
            // Reset password fields after successful update
            form.password = ''
            form.password_confirmation = ''
            
            // Show success toast
            if (window.toast) {
                window.toast.success('Profil berhasil diperbarui', 'Perubahan telah disimpan', 3000)
            }
        },
        onError: (serverErrors) => {
            processing.value = false
            errors.value = serverErrors
            
            // Show error toast
            if (window.toast) {
                window.toast.error('Gagal memperbarui profil', 'Periksa kembali data yang diisi', 4000)
            }
        },
        onFinish: () => {
            processing.value = false
        }
    })
}

const resetForm = () => {
    form.name = props.user.name
    form.email = props.user.email
    form.phone = props.user.phone
    form.password = ''
    form.password_confirmation = ''
    errors.value = {}
}

const formatRegistrationDate = (dateString) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    })
}

const logout = () => {
    router.post('/logout')
}
</script>