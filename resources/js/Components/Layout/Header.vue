<template>
    <header 
        id="navbar"
        class="fixed top-0 z-50 flex flex-wrap w-full text-sm transition-colors duration-500 bg-transparent backdrop-blur-md dark:border-gray-700 sm:justify-start sm:flex-nowrap sm:py-0"
    >
        <nav class="relative max-w-[85rem] w-full mx-auto px-4 sm:flex sm:items-center md:px-8 sm:justify-between sm:px-6 lg:px-16 xl:px-0 sm:gap-5">
            <div class="flex items-center justify-between py-3 overflow-hidden transition-all duration-300 basis-full grow">
                <!-- Logo -->
                <div class="bg-gradient-to-br bg-clip-text box-decoration-clone from-blue-400 to-blue-700 font-bold text-transparent !no-underline text-xl lowercase font-poppins text-2xl">
                    <Link href="/" :title="$page.props.settings.title">
                        <img 
                            :src="$page.props.settings.logo" 
                            :alt="$page.props.settings.title" 
                            class="h-10 image-cover"
                            onerror="this.onerror=null;this.src='/images/logo.png'"
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
                        Home
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
                <div class="flex items-center gap-x-2">
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
                        class="btn px-4 py-2 btn-primary" 
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
