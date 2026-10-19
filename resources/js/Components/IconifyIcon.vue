<template>
    <i 
        :data-icon="icon" 
        :style="{ width: width + 'px', height: height + 'px', fontSize: width + 'px' }"
        class="iconify inline-block"
        v-bind="$attrs"
    ></i>
</template>

<script setup>
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

<style>
.iconify {
    display: inline-block;
    vertical-align: middle;
}
</style>