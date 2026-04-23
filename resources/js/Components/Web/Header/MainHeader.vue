<template>
    <div class="bg-white border-b border-gray-100">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between gap-2 py-3">

                <!-- Logo -->
                <Link href="/" class="flex items-center gap-2.5 flex-shrink-0 group">
                    <!-- Desktop: katta logo -->
                    <img
                        src="/assets/logo.png"
                        alt="Yangi Asr Universiteti"
                        class="hidden lg:block h-11 w-auto"
                    >
                    <!-- Mobile: kichik logo -->
                    <img
                        src="/assets/logo.png"
                        alt="Yangi Asr Universiteti"
                        class="lg:hidden h-8 w-auto"
                    >

                </Link>

                <!-- Search (faqat desktop) -->
                <div class="hidden lg:flex flex-1 max-w-xl mx-4">
                    <div class="relative w-full">
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Qidirish..."
                            class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#0f3460] focus:bg-white transition"
                            @keyup.enter="handleSearch"
                        >
                        <button
                            @click="handleSearch"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#0f3460] transition"
                        >
                            <Icon icon="mdi:magnify" class="w-5 h-5" />
                        </button>
                    </div>
                </div>

                <!-- O'ng qism -->
                <div class="flex items-center gap-1.5">

                    <!-- Search (mobile) icon -->
                    <button
                        @click="searchOpen = !searchOpen"
                        class="lg:hidden flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 hover:bg-gray-50 transition text-gray-600"
                    >
                        <Icon icon="mdi:magnify" class="w-4 h-4" />
                    </button>

                    <!-- Kontakt (mobile dropdown) -->
                    <div class="relative lg:hidden" ref="contactRef">
                        <button
                            @click="contactOpen = !contactOpen"
                            class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 hover:bg-gray-50 transition text-gray-600"
                        >
                            <Icon icon="fa6-solid:phone-volume" class="w-3.5 h-3.5" />
                        </button>
                        <div
                            v-if="contactOpen"
                            class="absolute right-0 top-full mt-2 w-56 bg-white rounded-xl border border-gray-100 shadow-xl py-2 z-50"
                        >
                            <a
                                :href="`tel:${settings.phone || '+998712077755'}`"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition"
                            >
                                <Icon icon="fa6-solid:phone-volume" class="w-4 h-4 text-[#0f3460]" />
                                {{ settings.phone || '+998 (71) 207-77-55' }}
                            </a>
                            <a
                                :href="`mailto:${settings.email || 'info@yaumd.uz'}`"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition"
                            >
                                <Icon icon="mdi:email-outline" class="w-4 h-4 text-[#0f3460]" />
                                {{ settings.email || 'info@yaumd.uz' }}
                            </a>
                        </div>
                    </div>

                    <!-- Kabinet dropdown -->
                    <div class="relative" ref="userRef">
                        <button
                            @click="userOpen = !userOpen"
                            class="flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 hover:bg-gray-50 transition text-gray-600 lg:w-auto lg:h-auto lg:gap-1.5 lg:px-3 lg:py-2 lg:text-sm"
                        >
                            <Icon icon="mdi:account-circle-outline" class="w-4 h-4 lg:w-5 lg:h-5" />
                            <span class="hidden lg:block">Kabinet</span>
                            <Icon icon="mdi:chevron-down" class="hidden lg:block w-3.5 h-3.5 transition-transform" :class="userOpen ? 'rotate-180' : ''" />
                        </button>
                        <div
                            v-if="userOpen"
                            class="absolute right-0 top-full mt-2 w-52 bg-white rounded-xl border border-gray-100 shadow-xl py-2 z-50"
                        >
                            <div class="px-4 py-2 border-b border-gray-100 mb-1">
                                <p class="text-xs text-gray-400">Shaxsiy kabinet</p>
                            </div>
                            <Link
                                href="/login"
                                @click="userOpen = false"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition"
                            >
                                <Icon icon="mdi:login" class="w-4 h-4 text-[#0f3460]" />
                                Kirish
                            </Link>
                            <Link
                                href="/qabul/ariza"
                                @click="userOpen = false"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition"
                            >
                                <Icon icon="mdi:school-outline" class="w-4 h-4 text-[#0f3460]" />
                                Ariza topshirish
                            </Link>
                        </div>
                    </div>

                    <!-- Qabul (faqat desktop) -->
                    <Link
                        href="/qabul/ariza"
                        class="hidden lg:flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-[#0f3460] to-[#533483] text-white text-sm font-medium rounded-xl hover:shadow-lg hover:shadow-[#0f3460]/25 transition-all"
                    >
                        <Icon icon="mdi:school-outline" class="w-4 h-4" />
                        Qabul 2026
                    </Link>

                    <!-- Mobile burger -->
                    <button
                        @click="$emit('toggleMobile')"
                        class="lg:hidden flex items-center justify-center w-8 h-8 rounded-lg border border-gray-200 hover:bg-gray-50 transition"
                    >
                        <Icon v-if="!mobileOpen" icon="mdi:menu" class="w-5 h-5 text-gray-600" />
                        <Icon v-else icon="mdi:close" class="w-5 h-5 text-gray-600" />
                    </button>

                </div>
            </div>

            <!-- Search (mobile) ochiladi -->
            <div v-if="searchOpen" class="lg:hidden pb-3">
                <div class="relative">
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Qidirish..."
                        class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-[#0f3460] transition"
                        @keyup.enter="handleSearch"
                        ref="searchInput"
                    >
                    <button class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <Icon icon="mdi:magnify" class="w-5 h-5" />
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, watch, nextTick, onMounted, onUnmounted } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

defineProps({
    settings:    { type: Object, default: () => ({}) },
    currentLang: { type: String, default: 'uz' },
    mobileOpen:  { type: Boolean, default: false },
})

const emit = defineEmits(['toggleMobile', 'changeLang'])

const searchQuery  = ref('')
const searchOpen   = ref(false)
const contactOpen  = ref(false)
const userOpen     = ref(false)
const searchInput  = ref(null)
const contactRef   = ref(null)
const userRef      = ref(null)

// Search ochilganda inputga focus
watch(searchOpen, async (val) => {
    if (val) {
        await nextTick()
        searchInput.value?.focus()
    }
})

const handleSearch = () => {
    if (searchQuery.value.trim()) {
        window.location.href = `/qidiruv?q=${encodeURIComponent(searchQuery.value)}`
    }
}

const handleOutsideClick = (e) => {
    if (contactRef.value && !contactRef.value.contains(e.target)) contactOpen.value = false
    if (userRef.value && !userRef.value.contains(e.target)) userOpen.value = false
}

onMounted(() => document.addEventListener('mousedown', handleOutsideClick))
onUnmounted(() => document.removeEventListener('mousedown', handleOutsideClick))
</script>
