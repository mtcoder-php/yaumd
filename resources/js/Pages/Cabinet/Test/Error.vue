<template>
    <div class="min-h-screen flex items-center justify-center p-4"
         style="background: linear-gradient(135deg, #0f3460 0%, #16213e 50%, #533483 100%)">

        <div class="text-center">
            <div class="w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6"
                 style="background: rgba(239,68,68,0.2); border: 2px solid rgba(239,68,68,0.4)">
                <Icon icon="mdi:alert-circle-outline" class="w-10 h-10 text-red-400" />
            </div>
            <h1 class="text-2xl font-bold text-white mb-3">Xatolik yuz berdi</h1>
            <p class="text-white/60 text-sm mb-8 max-w-sm mx-auto">{{ message }}</p>
            <a
                :href="route('cabinet.login')"
                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold text-sm"
                style="background: white; color: #0f3460"
            >
                <Icon icon="mdi:arrow-left" class="w-4 h-4" />
                Orqaga qaytish
            </a>
        </div>
    </div>
</template>

<script setup>
import { Icon } from '@iconify/vue'

defineProps({
    message: { type: String, default: 'Noma\'lum xatolik yuz berdi!' },
})
</script>
