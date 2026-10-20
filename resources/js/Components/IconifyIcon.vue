<template>
    <Icon
        :icon="icon"
        :width="width"
        :height="height ?? undefined"
        :ssr="true"
        class="inline-block align-middle"
    />
</template>

<script>
import { addCollection } from '@iconify/vue'
import collections from '@/icons.json'

collections.forEach((collection) => addCollection(collection))
</script>

<script setup>
import { Icon } from '@iconify/vue'

defineProps({
    icon: {
        type: String,
        required: true
    },
    width: {
        type: [Number, String],
        default: 24
    },
    height: {
        type: [Number, String],
        default: null
    }
})
</script>
