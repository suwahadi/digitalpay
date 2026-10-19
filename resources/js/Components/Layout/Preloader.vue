<template>
    <Transition 
        name="preloader-fade"
        enter-active-class="transition-opacity duration-300"
        leave-active-class="transition-opacity duration-500"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div 
            v-show="show" 
            class="fixed inset-0 bg-white dark:bg-gray-900 z-[99999] flex flex-col"
        >
            <!-- Header Skeleton -->
            <div class="bg-white dark:bg-gray-800 shadow-sm border-b dark:border-gray-700">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between h-16">
                        <!-- Logo Skeleton -->
                        <div class="flex items-center space-x-4">
                            <div class="w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                            <div class="w-32 h-6 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                        </div>
                        
                        <!-- Navigation Skeleton -->
                        <div class="hidden md:flex items-center space-x-6">
                            <div class="w-16 h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                            <div class="w-20 h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                            <div class="w-18 h-4 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                        </div>
                        
                        <!-- Actions Skeleton -->
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                            <div class="w-20 h-8 bg-gray-200 dark:bg-gray-700 rounded animate-pulse"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Skeleton -->
            <div class="flex-1 bg-gray-50 dark:bg-gray-900 overflow-y-auto">
                <!-- Hero Section Skeleton -->
                <div class="bg-white dark:bg-gray-800 py-8">
                    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div class="text-center space-y-4">
                            <div class="w-64 h-8 bg-gray-200 dark:bg-gray-700 rounded mx-auto animate-pulse"></div>
                            <div class="w-96 h-4 bg-gray-200 dark:bg-gray-700 rounded mx-auto animate-pulse"></div>
                            <div class="w-32 h-10 bg-gray-200 dark:bg-gray-700 rounded mx-auto animate-pulse"></div>
                        </div>
                    </div>
                </div>

                <!-- Grid Content Skeleton -->
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 space-y-16">
                    <!-- Section 1 -->
                    <div class="space-y-8">
                        <div class="text-center space-y-2">
                            <div class="w-48 h-6 bg-gray-200 dark:bg-gray-700 rounded mx-auto animate-pulse"></div>
                            <div class="w-72 h-4 bg-gray-200 dark:bg-gray-700 rounded mx-auto animate-pulse"></div>
                        </div>
                        
                        <!-- Grid Skeleton -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            <div 
                                v-for="n in 6" 
                                :key="`grid1-${n}`"
                                class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm animate-pulse"
                            >
                                <div class="space-y-4">
                                    <div class="w-full h-32 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                    <div class="space-y-2">
                                        <div class="w-3/4 h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                        <div class="w-1/2 h-3 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2 -->
                    <div class="space-y-8">
                        <div class="text-center space-y-2">
                            <div class="w-56 h-6 bg-gray-200 dark:bg-gray-700 rounded mx-auto animate-pulse"></div>
                            <div class="w-80 h-4 bg-gray-200 dark:bg-gray-700 rounded mx-auto animate-pulse"></div>
                        </div>
                        
                        <!-- Card Grid Skeleton -->
                        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
                            <div 
                                v-for="n in 8" 
                                :key="`grid2-${n}`"
                                class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm animate-pulse"
                            >
                                <div class="space-y-3">
                                    <div class="w-full h-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                    <div class="space-y-2">
                                        <div class="w-full h-3 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                        <div class="w-2/3 h-3 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ Section Skeleton -->
                    <div class="space-y-8">
                        <div class="text-center space-y-2">
                            <div class="w-64 h-6 bg-gray-200 dark:bg-gray-700 rounded mx-auto animate-pulse"></div>
                            <div class="w-96 h-4 bg-gray-200 dark:bg-gray-700 rounded mx-auto animate-pulse"></div>
                        </div>
                        
                        <div class="max-w-4xl mx-auto space-y-4">
                            <div 
                                v-for="n in 4" 
                                :key="`faq-${n}`"
                                class="bg-white dark:bg-gray-800 rounded-lg p-6 shadow-sm animate-pulse"
                            >
                                <div class="flex justify-between items-center">
                                    <div class="w-3/4 h-4 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                    <div class="w-6 h-6 bg-gray-200 dark:bg-gray-700 rounded"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer Skeleton -->
            <div class="bg-gray-800 text-white py-8">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                        <div 
                            v-for="n in 4" 
                            :key="`footer-${n}`"
                            class="space-y-3"
                        >
                            <div class="w-24 h-4 bg-gray-700 rounded animate-pulse"></div>
                            <div class="space-y-2">
                                <div class="w-32 h-3 bg-gray-700 rounded animate-pulse"></div>
                                <div class="w-28 h-3 bg-gray-700 rounded animate-pulse"></div>
                                <div class="w-36 h-3 bg-gray-700 rounded animate-pulse"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading Spinner Overlay -->
            <div class="absolute inset-0 flex items-center justify-center bg-white/80 dark:bg-gray-900/80">
                <div class="text-center space-y-4">
                    <!-- Logo Animation -->
                    <div class="relative">
                        <div class="w-16 h-16 mx-auto border-4 border-blue-200 dark:border-blue-800 rounded-full animate-pulse"></div>
                        <div class="absolute inset-0 w-16 h-16 mx-auto border-t-4 border-blue-600 dark:border-blue-400 rounded-full animate-spin"></div>
                    </div>
                    
                    <!-- Loading Text -->
                    <div class="space-y-2">
                        Loading...
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script setup>
defineProps({
    show: {
        type: Boolean,
        default: false
    }
})
</script>

<style scoped>
@keyframes bounce {
    0%, 80%, 100% {
        transform: translateY(0);
    }
    40% {
        transform: translateY(-10px);
    }
}

.animate-bounce {
    animation: bounce 1.4s infinite;
}
</style>