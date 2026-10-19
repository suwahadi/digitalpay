<template>
    <section class="w-full mx-auto max-w-[85rem] p-4 mt-20 sm:mt-28">
        <div class="swiper heroSwiper" ref="swiperContainer">
            <div class="swiper-wrapper">
                <div 
                    v-for="slide in slides" 
                    :key="slide.id" 
                    class="swiper-slide"
                >
                    <Link :href="slide.link">
                        <img 
                            :src="slide.image" 
                            :alt="slide.title"
                            class="w-full rounded-lg"
                            @error="handleImageError"
                        />
                    </Link>
                </div>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    </section>
</template>

<script setup>
import { onImageError } from '@/imageFallback'
import { onMounted, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Swiper } from 'swiper'
import { Navigation, Pagination, Autoplay } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'

defineProps({
    slides: Array,
})

const swiperContainer = ref(null)

onMounted(() => {
    new Swiper(swiperContainer.value, {
        modules: [Navigation, Pagination, Autoplay],
        loop: true,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            640: {
                slidesPerView: 1,
            },
            768: {
                slidesPerView: 1,
            },
            1024: {
                slidesPerView: 1,
            },
        },
    })
})

const handleImageError = (event) => {
    onImageError(event, '/storage/images/default.jpg')
}
</script>
