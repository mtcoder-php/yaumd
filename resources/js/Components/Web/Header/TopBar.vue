<template>
    <div class="bg-navy-900 border-b border-white/10">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between h-10 text-xs sm:text-sm">

                <!-- Chap: kontakt (desktop) -->
                <div class="hidden md:flex items-center gap-5">
                    <a
                        :href="`mailto:${settings.email || 'info@yangiasr.uz'}`"
                        class="flex items-center gap-2 text-white/70 hover:text-white transition font-medium"
                    >
                        <Icon icon="mdi:email-outline" class="w-4 h-4" />
                        {{ settings.email || 'info@yangiasr.uz' }}
                    </a>
                    <a
                        :href="`tel:${settings.phone || '+998712345678'}`"
                        class="flex items-center gap-2 text-white/70 hover:text-white transition font-medium"
                    >
                        <Icon icon="mdi:phone-outline" class="w-4 h-4" />
                        {{ settings.phone || '+998 71 234 56 78' }}
                    </a>
                </div>

                <!-- Mobil: soat o'rniga qisqa manzil -->
                <div class="flex md:hidden items-center gap-2 text-white/70 font-medium">
                    <Icon icon="mdi:email-outline" class="w-4 h-4" />
                    {{ settings.email || 'info@yangiasr.uz' }}
                </div>

                <!-- O'ng: utility havolalar + til -->
                <div class="flex items-center gap-4 sm:gap-5">
                    <div class="hidden lg:flex items-center gap-5">
                        <template v-for="(link, index) in utilityLinks" :key="link.title">
                            <span v-if="index > 0" class="hidden lg:block w-px h-4 bg-white/15"></span>
                            <a
                                :href="link.url"
                                class="text-white/70 hover:text-white transition font-medium"
                            >
                                {{ link.title }}
                            </a>
                        </template>
                    </div>

                    <span class="hidden lg:block w-px h-4 bg-white/15"></span>

                    <!-- Til tanlash -->
                    <div class="relative" ref="langMenuRef">
                        <button
                            type="button"
                            @click="langMenuOpen = !langMenuOpen"
                            class="flex items-center gap-1.5 text-white/80 hover:text-white transition font-semibold"
                        >
                            <Icon icon="mdi:web" class="w-4 h-4" />
                            {{ currentLangLabel }}
                            <Icon icon="mdi:chevron-down" class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': langMenuOpen }" />
                        </button>

                        <div
                            v-if="langMenuOpen"
                            class="absolute right-0 top-full mt-2 py-1 w-24 bg-white rounded-lg shadow-lg border border-gray-100 z-50"
                        >
                            <button
                                v-for="l in langs"
                                :key="l.code"
                                @click="selectLang(l.code)"
                                class="w-full text-left px-3 py-1.5 text-sm font-medium transition"
                                :class="currentLang === l.code
                                    ? 'text-navy-700 bg-navy-50 font-semibold'
                                    : 'text-gray-600 hover:bg-gray-50'"
                            >
                                {{ l.full }}
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Icon } from '@iconify/vue'

const props = defineProps({
    settings:    { type: Object, default: () => ({}) },
    currentLang: { type: String, default: 'uz' },
})

const emit = defineEmits(['changeLang'])

const langMenuOpen = ref(false)
const langMenuRef  = ref(null)

// MUHIM: dropdown ochiq holatda sahifaning istalgan boshqa joyiga bosilsa
// yopilib ketishi kerak — shu uchun butun document'ga "click" tinglovchisi
// qo'yiladi va bosilgan joy dropdown konteyneri (tugma + ro'yxat) ICHIDA
// bo'lmasa, menyu yopiladi. Tugmaning o'zini bosish shu konteyner ICHIDA
// hisoblanadi, shuning uchun ochish/yopish albatta to'g'ri ishlayveradi.
const handleClickOutside = (event) => {
    if (langMenuOpen.value && langMenuRef.value && !langMenuRef.value.contains(event.target)) {
        langMenuOpen.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})
onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
})

const langs = [
    { code: 'uz', label: 'UZ', full: "O'zbekcha" },
    { code: 'ru', label: 'РУ', full: 'Русский' },
    { code: 'en', label: 'EN', full: 'English' },
]

const utilityLinks = [
    { title: 'Talabalar uchun',    url: '#' },
    { title: "O'qituvchilar uchun", url: '#' },
    { title: 'Karyera',            url: '#' },
    { title: 'Sayt xaritasi',      url: '#' },
]

const currentLangLabel = computed(() => langs.find(l => l.code === props.currentLang)?.label || 'UZ')

const selectLang = (code) => {
    langMenuOpen.value = false
    emit('changeLang', code)
}
</script>
