<template>
    <section class="py-16 bg-gray-50 dark:bg-gray-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                    Berita & Update Terbaru
                </h2>
                <p class="text-lg text-gray-600 dark:text-gray-400">
                    Tetap update dengan informasi terbaru dari {{ $page.props.settings?.title || 'DigitalPay' }}
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <article 
                    v-for="article in articles" 
                    :key="article.id"
                    class="bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-shadow duration-300"
                >
                    <!-- Image as clickable link -->
                    <Link :href="article.url || route('news.show', { slug: article.slug })" class="block">
                        <img
                            loading="lazy"
                            decoding="async"
                            :src="getImageUrl(article.featured_image || article.featured_image_url)"
                            :alt="article.title"
                            class="w-full h-48 object-cover hover:scale-105 transition-transform duration-300"
                            @error="onImageError($event, '/images/placeholder.png')"
                        />
                    </Link>
                    <div class="p-6">
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400 mb-2">
                            <IconifyIcon icon="lucide:calendar" width="16" class="mr-1" />
                            {{ article.published_date }}
                        </div>
                        <!-- Title as clickable link -->
                        <Link :href="article.url || route('news.show', { slug: article.slug })" class="block">
                            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-3 hover:text-blue-600 transition-colors duration-300">
                                {{ article.title }}
                            </h3>
                        </Link>
                        <p class="text-gray-600 dark:text-gray-400 mb-4">
                            {{ article.excerpt }}
                        </p>
                        <Link
                            :href="article.url || route('news.show', { slug: article.slug })"
                            class="inline-flex min-h-[44px] items-center font-semibold text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300"
                        >
                            Baca Selengkapnya
                            <IconifyIcon icon="lucide:arrow-right" width="16" class="ml-1" />
                        </Link>
                    </div>
                </article>
            </div>

            <div class="text-center mt-8">
                <Link
                    :href="route('news.index')"
                    class="btn btn-secondary"
                >
                    Lihat Semua Berita
                </Link>
            </div>
        </div>
    </section>
</template>

<script setup>
import { onImageError } from '@/imageFallback'
import { Link } from '@inertiajs/vue3'

defineProps({
    articles: {
        type: Array,
        default: () => []
    }
})

const getImageUrl = (imagePath) => {
    if (!imagePath) return '/images/placeholder.png'
    
    if (imagePath.startsWith('http')) {
        return imagePath
    }
    
    if (imagePath.includes('storage/app/public/')) {
        return imagePath.replace(/.*storage\/app\/public\//, '/storage/')
    }
    
    if (imagePath.startsWith('articles/')) {
        return '/storage/' + imagePath
    }
    
    if (imagePath.startsWith('/')) {
        return imagePath
    }
    
    return '/storage/' + imagePath
}
</script>