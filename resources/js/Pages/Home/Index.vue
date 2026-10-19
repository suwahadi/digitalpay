<template>
    <AppLayout :settings="settings">
        <Head :title="sitetagline" />

        <!-- Hero Section -->
        <HeroSection :slides="hero.slides" />
        
        <!-- Event Reward Section -->
        <!-- <EventRewardSection /> -->
        
        <!-- Popular Products Section -->
        <PopularProductsSection :products="popularProducts" />
        
        <!-- Why Register Section -->
        <WhyRegisterSection />
        
        <!-- News Section -->
        <NewsSection :articles="recentNews" />
        
        <!-- FAQ Section -->
        <FaqSection :faqs="faqs" />
        
        <!-- Payment Methods Section -->
        <PaymentMethodsSection :methods="paymentMethods" />
    </AppLayout>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import HeroSection from '@/Components/Home/HeroSection.vue'
import EventRewardSection from '@/Components/Home/EventRewardSection.vue'
import PopularProductsSection from '@/Components/Home/PopularProductsSection.vue'
import WhyRegisterSection from '@/Components/Home/WhyRegisterSection.vue'
import NewsSection from '@/Components/Home/NewsSection.vue'
import FaqSection from '@/Components/Home/FaqSection.vue'
import PaymentMethodsSection from '@/Components/Home/PaymentMethodsSection.vue'

defineProps({
    title: String,
    sitetagline: String,
    hero: Object,
    popularProducts: Array,
    recentNews: Array,
    faqs: Array,
    paymentMethods: Array,
    settings: Object,
})
</script>
