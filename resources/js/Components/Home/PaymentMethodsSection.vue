<template>
    <section v-if="paymentMethods.length" class="py-16 bg-gray-50 dark:bg-gray-900" aria-labelledby="payment-methods-heading">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 id="payment-methods-heading" class="mb-10 text-center text-3xl font-bold text-gray-900 dark:text-white">
                Metode Pembayaran
            </h2>

            <ul class="flex flex-wrap justify-center gap-3">
                <li
                    v-for="method in paymentMethods"
                    :key="method.id"
                    class="flex h-16 w-[6.5rem] items-center justify-center rounded-xl border border-gray-200 bg-white px-3 sm:w-32 dark:border-gray-700"
                >
                    <img
                        v-if="method.logo && !failedLogos.has(method.id)"
                        :src="method.logo"
                        :alt="method.name"
                        loading="lazy"
                        class="max-h-8 w-auto max-w-full object-contain"
                        @error="failedLogos.add(method.id)"
                    />
                    <span v-else class="text-center text-xs font-semibold text-gray-900">
                        {{ method.name }}
                    </span>
                </li>
            </ul>
        </div>
    </section>
</template>

<script setup>
import { reactive } from 'vue'

defineProps({
    paymentMethods: {
        type: Array,
        default: () => [],
    },
})

const failedLogos = reactive(new Set())
</script>
