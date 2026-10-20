<template>
    <section class="mx-auto mt-20 w-full max-w-7xl px-4 py-4 sm:mt-28 sm:px-6 lg:px-8">
        <div
            v-if="slides?.length"
            ref="swiperContainer"
            class="swiper heroSwiper rounded-lg overflow-hidden"
        >
            <div class="swiper-wrapper">
                <div v-for="(slide, i) in slides" :key="slide.id" class="swiper-slide">
                    <Link :href="slide.link || '#'" class="block">
                        <img
                            :src="slide.image"
                            :alt="slide.title || 'DigitalPay'"
                            class="w-full aspect-video object-cover"
                            width="1920"
                            height="1080"
                            :fetchpriority="i === 0 ? 'high' : 'auto'"
                            :loading="i === 0 ? 'eager' : 'lazy'"
                            decoding="async"
                            @error="onImageError($event, '/images/placeholder.png')"
                        />
                    </Link>
                </div>
            </div>
            <div v-show="slides.length > 1" class="swiper-pagination"></div>
        </div>
    </section>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { onImageError } from '@/imageFallback'
import 'swiper/css'
import 'swiper/css/pagination'

const props = defineProps({
    slides: Array,
})

const swiperContainer = ref(null)

onMounted(async () => {
    if (!props.slides || props.slides.length < 2) {
        return
    }

    const { Swiper } = await import('swiper')
    const { Pagination, Autoplay } = await import('swiper/modules')

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches

    new Swiper(swiperContainer.value, {
        modules: [Pagination, Autoplay],
        loop: true,
        autoplay: reduceMotion ? false : { delay: 5000, disableOnInteraction: false },
        pagination: { el: '.swiper-pagination', clickable: true },
    })
})
</script>
