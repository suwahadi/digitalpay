<template>
    <section class="w-full mx-auto max-w-[85rem] p-4 mt-20 sm:mt-28">
        <component
            v-if="slide"
            :is="hasLink ? Link : 'div'"
            v-bind="hasLink ? { href: slide.link } : {}"
            class="block overflow-hidden rounded-lg"
        >
            <img
                :src="slide.image"
                :alt="slide.title || 'DigitalPay'"
                class="w-full aspect-[16/6] object-cover"
                width="1600"
                height="600"
                fetchpriority="high"
                decoding="async"
                @error="onImageError($event, '/images/placeholder.png')"
            />
        </component>
    </section>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { onImageError } from '@/imageFallback'

const props = defineProps({
    slides: Array,
})

const slide = computed(() => props.slides?.[0] ?? null)
const hasLink = computed(() => !!slide.value?.link && slide.value.link !== '#')
</script>
