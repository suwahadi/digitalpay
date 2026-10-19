<template>
    <button
        @click="toggleDarkMode"
        class="btn-icon"
        :title="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
    >
        <IconifyIcon 
            :icon="isDark ? 'lucide:sun' : 'lucide:moon'" 
            width="18" 
        />
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
    // Check for saved theme preference or default to light mode
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