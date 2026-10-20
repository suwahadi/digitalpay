<template>
    <button
        type="button"
        @click="toggleDarkMode"
        class="btn-icon"
        :aria-label="isDark ? 'Ganti ke mode terang' : 'Ganti ke mode gelap'"
        :title="isDark ? 'Ganti ke mode terang' : 'Ganti ke mode gelap'"
    >
        <span :key="isDark ? 'sun' : 'moon'" class="inline-flex">
            <IconifyIcon :icon="isDark ? 'lucide:sun' : 'lucide:moon'" width="18" />
        </span>
    </button>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const isDark = ref(false)

const toggleDarkMode = () => {
    isDark.value = !isDark.value
    
    if (isDark.value) {
        document.documentElement.classList.add('dark')
        localStorage.setItem('theme', 'dark')
    } else {
        document.documentElement.classList.remove('dark')
        localStorage.setItem('theme', 'light')
    }
}

onMounted(() => {
    const savedTheme = localStorage.getItem('theme')
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches
    
    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        isDark.value = true
        document.documentElement.classList.add('dark')
    } else {
        isDark.value = false
        document.documentElement.classList.remove('dark')
    }
})
</script>