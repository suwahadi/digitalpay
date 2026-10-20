<template>
    <footer class="bg-gray-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <!-- Company Info -->
                <div class="col-span-1 md:col-span-2">
                    <div class="mb-4">
                        <Link href="/" :title="$page.props.settings.title">
                            <img 
                                loading="lazy"
                                decoding="async"
                                :src="$page.props.settings.logo" 
                                :alt="$page.props.settings.title"
                                class="h-10 mb-4"
                                onerror="this.onerror=null;this.src='/images/logo.png'"
                            />
                        </Link>
                    </div>
                    <p class="text-gray-400 mb-4 max-w-md">
                        {{ $page.props.settings.description }}
                    </p>
                    <div class="flex space-x-4">
                        <a 
                            v-if="$page.props.settings.social.facebook" 
                            :href="$page.props.settings.social.facebook" 
                            target="_blank"
                            class="text-gray-400 hover:text-white transition-colors"
                        >
                            <IconifyIcon icon="lucide:facebook" width="20" />
                        </a>
                        <a 
                            v-if="$page.props.settings.social.twitter" 
                            :href="$page.props.settings.social.twitter" 
                            target="_blank"
                            class="text-gray-400 hover:text-white transition-colors"
                        >
                            <IconifyIcon icon="lucide:twitter" width="20" />
                        </a>
                        <a 
                            v-if="$page.props.settings.social.instagram" 
                            :href="$page.props.settings.social.instagram" 
                            target="_blank"
                            class="text-gray-400 hover:text-white transition-colors"
                        >
                            <IconifyIcon icon="lucide:instagram" width="20" />
                        </a>
                        <a 
                            v-if="$page.props.settings.social.youtube" 
                            :href="$page.props.settings.social.youtube" 
                            target="_blank"
                            class="text-gray-400 hover:text-white transition-colors"
                        >
                            <IconifyIcon icon="lucide:youtube" width="20" />
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Tautan Cepat</h3>
                    <ul class="space-y-2">
                        <li>
                            <Link href="/" class="text-gray-400 hover:text-white transition-colors">
                                Beranda
                            </Link>
                        </li>
                        <li>
                            <Link href="/products" class="text-gray-400 hover:text-white transition-colors">
                                Produk
                            </Link>
                        </li>
                        <li>
                            <Link href="/about" class="text-gray-400 hover:text-white transition-colors">
                                Tentang Kami
                            </Link>
                        </li>
                        <li>
                            <Link href="/contact" class="text-gray-400 hover:text-white transition-colors">
                                Kontak
                            </Link>
                        </li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h3 class="text-lg font-semibold mb-4">Bantuan</h3>
                    <ul class="space-y-2">
                        <li>
                            <Link href="/faq" class="text-gray-400 hover:text-white transition-colors">
                                FAQ
                            </Link>
                        </li>
                        <li>
                            <Link href="/help" class="text-gray-400 hover:text-white transition-colors">
                                Pusat Bantuan
                            </Link>
                        </li>
                        <li>
                            <Link href="/terms" class="text-gray-400 hover:text-white transition-colors">
                                Syarat & Ketentuan
                            </Link>
                        </li>
                        <li>
                            <Link href="/privacy" class="text-gray-400 hover:text-white transition-colors">
                                Kebijakan Privasi
                            </Link>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Section -->
            <div class="border-t border-gray-800 mt-8 pt-8">
                <p class="text-gray-400 text-sm text-center">
                    Copyright &copy; {{ currentYear }} {{ $page.props.settings.title }}
                </p>
            </div>
        </div>
    </footer>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import IconifyIcon from '../IconifyIcon.vue'
const currentYear = computed(() => new Date().getFullYear())
</script>