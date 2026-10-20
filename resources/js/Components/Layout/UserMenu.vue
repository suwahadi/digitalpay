<template>
    <div class="relative">
        <button
            @click="toggleMenu"
            class="flex items-center gap-2 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
        >
            <img
                :src="user.avatar || 'https://ui-avatars.com/api/?name='+ user.name +'&background=667eea&color=fff'"
                :alt="user.name"
                class="w-8 h-8 rounded-full object-cover"
            />
            <span class="hidden sm:block text-sm font-medium text-gray-700 dark:text-gray-300">
                {{ user.name }}
            </span>
            <span class="inline-flex transition-transform duration-200" :class="{ 'rotate-180': isMenuOpen }">
                <IconifyIcon icon="lucide:chevron-down" width="16" />
            </span>
        </button>

        <!-- Dropdown Menu -->
        <Teleport to="body">
            <div
                v-if="isMenuOpen"
                ref="dropdownMenu"
                data-dropdown
                class="fixed w-48 bg-white border border-gray-200 rounded-lg shadow-xl dark:bg-gray-800 dark:border-gray-600 transition-all duration-200 ease-out"
                style="z-index: 99999;"
                :style="dropdownStyle"
            >
            <div class="py-1">
                <Link
                    :href="route('user.dashboard')"
                    class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                    @click="isMenuOpen = false"
                >
                    <IconifyIcon icon="lucide:layout-dashboard" width="16" class="mr-3" />
                    Dasbor
                </Link>
                
                <Link
                    :href="route('user.history')"
                    class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                    @click="isMenuOpen = false"
                >
                    <IconifyIcon icon="lucide:shopping-bag" width="16" class="mr-3" />
                    Riwayat Pesanan
                </Link>
                
                <Link
                    :href="route('user.profile')"
                    class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors"
                    @click="isMenuOpen = false"
                >
                    <IconifyIcon icon="lucide:user" width="16" class="mr-3" />
                    Profil
                </Link>
                
                <div class="border-t border-gray-200 dark:border-gray-600 my-1"></div>
                
                <button
                    @click="logout"
                    class="flex items-center w-full px-4 py-3 text-sm font-semibold text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 transition-colors"
                >
                    <IconifyIcon icon="lucide:log-out" width="16" class="mr-3" />
                    Logout
                </button>
            </div>
            </div>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, computed, inject } from 'vue'
import { Link, router } from '@inertiajs/vue3'

defineProps({
    user: {
        type: Object,
        required: true
    }
})

const route = inject('route')
const isMenuOpen = ref(false)
const dropdownMenu = ref(null)
const dropdownPosition = ref({ top: 0, left: 0 })

const dropdownStyle = computed(() => ({
    top: `${dropdownPosition.value.top}px`,
    left: `${dropdownPosition.value.left}px`
}))

const toggleMenu = async (event) => {
    isMenuOpen.value = !isMenuOpen.value
    
    if (isMenuOpen.value) {
        updateDropdownPosition(event)
    }
}

const updateDropdownPosition = (event) => {
    const button = event.currentTarget
    const rect = button.getBoundingClientRect()
    const dropdownWidth = 192 // w-48 = 12rem = 192px
    const viewportWidth = window.innerWidth
    const viewportHeight = window.innerHeight
    
    let left = rect.right - dropdownWidth
    if (left < 8) { // 8px margin from edge
        left = rect.left
    }
    if (left + dropdownWidth > viewportWidth - 8) {
        left = viewportWidth - dropdownWidth - 8
    }
    
    let top = rect.bottom + 8 // 8px gap
    const dropdownHeight = 200 // Estimated height
    if (top + dropdownHeight > viewportHeight - 8) {
        top = rect.top - dropdownHeight - 8 // Show above if not enough space below
    }
    
    dropdownPosition.value = { top, left }
}

const logout = () => {
    router.post(route('logout'))
}

const handleClickOutside = (event) => {
    const userMenu = event.target.closest('.relative')
    const dropdown = event.target.closest('[data-dropdown]')
    
    if (!userMenu && !dropdown) {
        isMenuOpen.value = false
    }
}

const handleResize = () => {
    if (isMenuOpen.value) {
        isMenuOpen.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
    window.addEventListener('resize', handleResize)
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
    window.removeEventListener('resize', handleResize)
})
</script>