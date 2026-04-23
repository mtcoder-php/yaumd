<template>
    <div class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-11">

                <!-- Chap: Reklama (faqat desktop) -->
                <div class="hidden lg:flex items-center gap-3">
                    <span class="flex items-center gap-2 text-[#0f3460] font-semibold text-sm">
                        <Icon icon="mdi:bell-ring" class="w-4 h-4 text-red-500" />
                        2026-yil qabul boshlandi!
                    </span>
                    <span class="text-gray-300">|</span>
                    <span class="text-gray-500 text-sm">
                        Talaba bo'ling va 50% gacha chegirma qo'lga kiriting
                    </span>
                </div>

                <!-- O'ng -->
                <div class="flex items-center gap-4 ml-auto">

                    <!-- Kontakt (faqat desktop) -->
                    <div class="hidden lg:flex items-center gap-5">
                        <a
                            :href="`tel:${settings.phone || '+998712077755'}`"
                            class="flex items-center gap-2 text-gray-600 hover:text-[#0f3460] transition text-sm font-medium"
                        >
                            <Icon icon="fa6-solid:phone-volume" class="w-3.5 h-3.5" />
                            {{ settings.phone || '+998 (71) 207-77-55' }}
                        </a>
                        <a
                            :href="`mailto:${settings.email || 'info@yaumd.uz'}`"
                            class="flex items-center gap-2 text-gray-600 hover:text-[#0f3460] transition text-sm font-medium"
                        >
                            <Icon icon="mdi:email-outline" class="w-4 h-4" />
                            {{ settings.email || 'info@yangiasr.uz' }}
                        </a>
                        <span class="w-px h-5 bg-gray-200"></span>
                    </div>

                    <!-- Ijtimoiy (faqat desktop) -->
                    <div class="hidden lg:flex items-center gap-2">
                        <a
                            v-for="s in socials"
                            :key="s.name"
                            :href="s.url"
                            target="_blank"
                            :title="s.name"
                            class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-500 hover:text-[#0f3460] hover:bg-gray-100 transition"
                        >
                            <Icon :icon="s.icon" class="w-4 h-4" />
                        </a>
                        <span class="w-px h-5 bg-gray-200 ml-1"></span>
                    </div>

                    <!-- Soat — desktop va mobile -->
                    <div class="flex items-center gap-2 text-gray-600 text-sm font-medium">
                        <Icon icon="mdi:clock-outline" class="w-4 h-4" />
                        <span class="font-mono tracking-wider">{{ currentTime }}</span>
                    </div>

                    <span class="w-px h-5 bg-gray-200"></span>

                    <!-- Til tanlash — desktop va mobile -->
                    <div class="flex items-center gap-0.5 bg-gray-100 rounded-lg p-0.5">
                        <button
                            v-for="l in langs"
                            :key="l.code"
                            @click="$emit('changeLang', l.code)"
                            class="px-3 py-1 rounded-md text-xs font-semibold transition"
                            :class="currentLang === l.code
                                ? 'bg-white text-[#0f3460] shadow-sm'
                                : 'text-gray-500 hover:text-gray-700'"
                        >
                            {{ l.label }}
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Icon } from '@iconify/vue'

defineProps({
    settings:    { type: Object, default: () => ({}) },
    currentLang: { type: String, default: 'uz' },
})

defineEmits(['changeLang'])

const langs = [
    { code: 'uz', label: "O'z" },
    { code: 'ru', label: 'Ру' },
    { code: 'en', label: 'En' },
]

const socials = [
    { name: 'Telegram',  icon: 'mdi:telegram',  url: '#' },
    { name: 'Instagram', icon: 'mdi:instagram',  url: '#' },
    { name: 'Facebook',  icon: 'mdi:facebook',   url: '#' },
    { name: 'YouTube',   icon: 'mdi:youtube',    url: '#' },
]

const currentTime = ref('')
let timer = null

const updateTime = () => {
    const now = new Date()
    currentTime.value = now.toLocaleTimeString('uz-UZ', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
    })
}

onMounted(() => {
    updateTime()
    timer = setInterval(updateTime, 1000)
})

onUnmounted(() => clearInterval(timer))
</script>
