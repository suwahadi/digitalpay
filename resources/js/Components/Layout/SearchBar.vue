<template>
    <div :class="mobile ? 'relative w-full' : 'relative hidden md:block'">
        <div class="relative">
            <input
                ref="inputRef"
                v-model="searchQuery"
                type="text"
                placeholder="Cari produk..."
                :class="['pl-10 pr-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder-gray-400', mobile ? 'w-full py-3 text-base' : 'w-80 py-2 text-sm']"
                @keyup.enter="performSearch"
            />
            <div class="absolute inset-y-0 left-0 flex items-center pl-3">
                <IconifyIcon 
                    icon="lucide:search" 
                    class="w-4 h-4 text-gray-400" 
                />
            </div>
        </div>
        
        <!-- Search Results Dropdown -->
        <div 
            v-if="showResults && searchResults.length > 0"
            class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg dark:bg-gray-800 dark:border-gray-600"
        >
            <div class="max-h-60 overflow-y-auto">
                <div
                    v-for="result in searchResults"
                    :key="result.id"
                    @click="selectResult(result)"
                    class="flex items-center px-4 py-2 hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer"
                >
                    <img
                        :src="result.image"
                        :alt="result.name"
                        class="w-10 h-10 object-cover rounded-md mr-3"
                    />
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ result.name }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ formatPrice(result.price) }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue'
import { router } from '@inertiajs/vue3'
import IconifyIcon from '@/Components/IconifyIcon.vue'

const props = defineProps({
    mobile: { type: Boolean, default: false },
})
const emit = defineEmits(['navigate'])

const searchQuery = ref('')
const searchResults = ref([])
const showResults = ref(false)
const inputRef = ref(null)

watch(searchQuery, (newQuery) => {
    if (newQuery.length > 2) {
        searchProducts(newQuery)
    } else {
        searchResults.value = []
        showResults.value = false
    }
})

const searchProducts = async (query) => {
    try {
        const response = await fetch(`/api/products/search?q=${encodeURIComponent(query)}`)
        const data = await response.json()
        searchResults.value = data.products || []
        showResults.value = true
    } catch (error) {
        console.error('Search failed:', error)
        searchResults.value = []
        showResults.value = false
    }
}

const performSearch = () => {
    if (searchQuery.value.trim()) {
        router.visit(`/products?search=${encodeURIComponent(searchQuery.value)}`)
        showResults.value = false
        emit('navigate')
    }
}

const selectResult = (result) => {
    router.visit(`/product/${result.slug}`)
    showResults.value = false
    searchQuery.value = ''
    emit('navigate')
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
    }).format(price)
}

const handleClickOutside = (event) => {
    if (!event.target.closest('.relative')) {
        showResults.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
    if (props.mobile) {
        inputRef.value?.focus()
    }
})

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>