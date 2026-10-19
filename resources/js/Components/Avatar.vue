<template>
    <div
        :class="[
            'flex items-center justify-center rounded-full bg-gradient-to-br from-blue-500 to-purple-600 text-white font-semibold',
            sizeClass,
            borderClass
        ]"
        v-if="!imageUrl || imageError"
    >
        {{ initials }}
    </div>
    <img
        v-else
        :src="imageUrl"
        :alt="alt"
        :class="[
            'rounded-full object-cover',
            sizeClass,
            borderClass
        ]"
        @error="imageError = true"
    />
</template>

<script setup>
import { ref, computed } from 'vue'

const props = defineProps({
    name: {
        type: String,
        required: true
    },
    imageUrl: {
        type: String,
        default: null
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['xs', 'sm', 'md', 'lg', 'xl', '2xl'].includes(value)
    },
    border: {
        type: Boolean,
        default: false
    },
    borderColor: {
        type: String,
        default: 'border-white/20'
    },
    alt: {
        type: String,
        default: 'Avatar'
    }
})

const imageError = ref(false)

const initials = computed(() => {
    if (!props.name) return '??'
    
    const words = props.name.trim().split(' ')
    if (words.length === 1) {
        return words[0].substring(0, 2).toUpperCase()
    }
    
    return (words[0][0] + words[1][0]).toUpperCase()
})

const sizeClass = computed(() => {
    const sizes = {
        'xs': 'h-6 w-6 text-xs',
        'sm': 'h-8 w-8 text-sm',
        'md': 'h-10 w-10 text-sm',
        'lg': 'h-12 w-12 text-base',
        'xl': 'h-16 w-16 text-lg',
        '2xl': 'h-24 w-24 text-xl'
    }
    return sizes[props.size] || sizes.md
})

const borderClass = computed(() => {
    return props.border ? `border-4 ${props.borderColor}` : ''
})
</script>