<template>
    <header 
        id="navbar"
        class="fixed top-0 z-50 flex w-full border-b border-gray-200 bg-white text-sm dark:border-gray-800 dark:bg-gray-900"
    >
        <nav class="relative mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4 py-3">
                <!-- Logo -->
                <div class="shrink-0">
                    <Link href="/" :title="$page.props.settings.title" class="block">
                        <img
                            :src="$page.props.settings.logo"
                            :alt="$page.props.settings.title"
                            class="hidden h-8 w-auto min-[360px]:block sm:h-10"
                            width="185"
                            height="40"
                            onerror="this.onerror=null;this.src='/images/logo.png'"
                        />
                        <img
                            src="/images/logo-mark.webp"
                            :alt="$page.props.settings.title"
                            class="h-9 w-9 min-[360px]:hidden"
                            width="36"
                            height="36"
                        />
                    </Link>
                </div>

                <!-- Navigation Menu -->
                <nav class="hidden md:flex items-center space-x-8">
                    <Link 
                        href="/" 
                        class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors font-medium"
                        :class="{ 'text-blue-600 dark:text-blue-400': $page.url === '/' }"
                    >
                        Beranda
                    </Link>
                    <Link 
                        href="/products" 
                        class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors font-medium"
                        :class="{ 'text-blue-600 dark:text-blue-400': $page.url.startsWith('/products') || $page.url.startsWith('/product') }"
                    >
                        Produk
                    </Link>
                    <Link 
                        href="/news" 
                        class="text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors font-medium"
                        :class="{ 'text-blue-600 dark:text-blue-400': $page.url.startsWith('/news') }"
                    >
                        Berita
                    </Link>
                </nav>

                <!-- Search Bar -->
                <SearchBar />

                <!-- Right Side -->
                <div class="flex items-center gap-x-1 sm:gap-x-2">
                    <!-- Mobile Search Toggle -->
                    <button
                        type="button"
                        class="btn-icon md:hidden"
                        aria-label="Cari produk"
                        @click="showSearch = true"
                    >
                        <IconifyIcon icon="lucide:search" width="18" />
                    </button>

                    <!-- Dark Mode Toggle -->
                    <DarkModeToggle />

                    <!-- Login Button -->
                    <Link 
                        v-if="!$page.props.auth.user"
                        class="btn btn-primary inline-flex min-h-[44px] items-center px-3 py-2 sm:px-4"
                        :href="route('login')"
                    >
                        Masuk
                    </Link>
                    
                    <!-- User Menu -->
                    <UserMenu 
                        v-else 
                        :user="$page.props.auth.user" 
                    />
                </div>
            </div>
        </nav>

        <Teleport to="body">
            <div
                v-if="showSearch"
                class="fixed inset-0 z-[60] bg-black/50 backdrop-blur-sm px-4 pt-20 md:hidden"
                @click.self="showSearch = false"
            >
                <div class="max-w-lg mx-auto rounded-xl bg-white p-4 shadow-xl dark:bg-gray-800">
                    <div class="flex items-center gap-2">
                        <SearchBar mobile class="flex-1" @navigate="showSearch = false" />
                        <button
                            type="button"
                            class="btn-icon shrink-0"
                            aria-label="Tutup pencarian"
                            @click="showSearch = false"
                        >
                            <IconifyIcon icon="lucide:x" width="20" />
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </header>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import SearchBar from './SearchBar.vue'
import DarkModeToggle from './DarkModeToggle.vue'
import UserMenu from './UserMenu.vue'
import IconifyIcon from '../IconifyIcon.vue'

const showSearch = ref(false)

const onKeydown = (e) => {
    if (e.key === 'Escape') {
        showSearch.value = false
    }
}

onMounted(() => document.addEventListener('keydown', onKeydown))
onUnmounted(() => document.removeEventListener('keydown', onKeydown))
</script>
