<template>
    <section v-if="groups.length" class="py-16 bg-gray-50 dark:bg-gray-900" aria-labelledby="payment-methods-heading">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 id="payment-methods-heading" class="text-3xl font-bold text-gray-900 dark:text-white">
                    Metode Pembayaran
                </h2>
                <p class="mt-3 text-gray-600 dark:text-gray-400">
                    Semua metode di bawah ini tersedia saat checkout.
                </p>
            </div>

            <div class="flex flex-wrap justify-center gap-x-12 gap-y-8">
                <div v-for="group in groups" :key="group.label" class="w-full sm:w-auto">
                    <h3 class="mb-3 text-sm font-medium text-gray-600 dark:text-gray-400">
                        {{ group.label }}
                    </h3>
                    <ul class="grid grid-cols-3 gap-3 sm:flex">
                        <li
                            v-for="method in group.methods"
                            :key="method.id"
                            class="flex h-16 w-full items-center justify-center rounded-xl border border-gray-200 bg-white px-3 sm:w-32 dark:border-gray-700"
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
            </div>
        </div>
    </section>
</template>

<script setup>
import { computed, reactive } from 'vue'

const props = defineProps({
    paymentMethods: {
        type: Array,
        default: () => [],
    },
})

const GROUPS = [
    ['qris', 'QRIS'],
    ['ewallet', 'E-Wallet'],
    ['virtual_account', 'Virtual Account'],
    ['bank_transfer', 'Transfer Bank'],
]

const failedLogos = reactive(new Set())

const groups = computed(() => {
    const known = GROUPS.map(([type, label]) => ({
        label,
        methods: props.paymentMethods.filter(method => method.type === type),
    }))
    const others = props.paymentMethods.filter(method => !GROUPS.some(([type]) => type === method.type))

    return [...known, { label: 'Lainnya', methods: others }].filter(group => group.methods.length)
})
</script>
