<template>
    <div class="bg-white border-b border-gray-100 sticky top-0 z-40 transition-shadow" :class="scrolled ? 'shadow-md' : ''">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between gap-3 py-2.5">

                <!-- Logo -->
                <Link href="/" class="flex items-center flex-shrink-0">
                    <img src="/assets/logo-black.png" alt="Yangi Asr Universiteti" class="h-12 md:h-14 w-auto">
                </Link>

                <!-- Desktop menyu -->
                <nav class="hidden lg:flex items-center gap-1 flex-1 justify-center">
                    <div
                        v-for="item in menuItems"
                        :key="item.path"
                        class="relative group"
                    >
                        <Link
                            :href="item.href || '#'"
                            class="flex items-center gap-1 px-3 py-2 rounded-lg text-sm font-semibold text-gray-700 hover:text-brand-600 hover:bg-navy-50 transition whitespace-nowrap"
                            :class="isActive(item.href) ? 'text-brand-600 bg-navy-50' : ''"
                        >
                            {{ item.name }}
                            <Icon
                                v-if="item.submenu"
                                icon="mdi:chevron-down"
                                class="w-3.5 h-3.5 transition-transform group-hover:rotate-180"
                            />
                        </Link>

                        <!-- Mega dropdown -->
                        <div
                            v-if="item.submenu"
                            class="absolute top-full left-0 bg-white border border-gray-100 shadow-2xl rounded-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible translate-y-1 group-hover:translate-y-0 transition-all duration-200 z-50 mt-1"
                            style="min-width: 240px"
                        >
                            <ul class="py-2">
                                <li v-for="sub in item.submenu" :key="sub.path" class="relative group/sub">
                                    <div
                                        v-if="sub.innersubmenu"
                                        class="flex items-center justify-between px-4 py-2.5 text-sm text-gray-600 hover:text-brand-600 hover:bg-navy-50 transition cursor-default"
                                    >
                                        <span>{{ sub.title }}</span>
                                        <Icon icon="mdi:chevron-right" class="w-4 h-4 text-gray-400 flex-shrink-0" />

                                        <div
                                            class="absolute left-full top-0 bg-white border border-gray-100 shadow-2xl rounded-xl py-2 opacity-0 invisible group-hover/sub:opacity-100 group-hover/sub:visible transition-all duration-200 z-50"
                                            style="min-width: 260px; margin-left: 1px"
                                        >
                                            <div class="px-4 py-2 border-b border-gray-100 mb-1">
                                                <p class="text-xs font-semibold text-brand-600 uppercase tracking-wide">{{ sub.title }}</p>
                                            </div>
                                            <Link
                                                v-for="inner in sub.innersubmenu"
                                                :key="inner.path"
                                                :href="inner.path"
                                                class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-600 hover:text-brand-600 hover:bg-navy-50 transition"
                                            >
                                                <Icon icon="mdi:chevron-right" class="w-3.5 h-3.5 text-gray-300" />
                                                {{ inner.title }}
                                            </Link>
                                        </div>
                                    </div>

                                    <Link
                                        v-else
                                        :href="sub.path"
                                        class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-600 hover:text-brand-600 hover:bg-navy-50 transition"
                                    >
                                        <Icon icon="mdi:chevron-right" class="w-3.5 h-3.5 text-gray-300" />
                                        {{ sub.title }}
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </div>
                </nav>

                <!-- O'ng qism -->
                <div class="flex items-center gap-1.5 flex-shrink-0">

                    <!-- Search (desktop) -->
                    <div class="hidden lg:block relative" ref="searchRef">
                        <button
                            @click="searchOpen = !searchOpen"
                            class="flex items-center justify-center w-10 h-10 rounded-full border border-gray-200 hover:bg-gray-50 transition text-gray-600"
                        >
                            <Icon icon="mdi:magnify" class="w-4 h-4" />
                        </button>
                        <div
                            v-if="searchOpen"
                            class="absolute right-0 top-full mt-2 w-72 bg-white rounded-xl border border-gray-100 shadow-xl p-3 z-50"
                        >
                            <div class="relative">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="Qidirish..."
                                    class="w-full pl-3 pr-9 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-navy-500 transition"
                                    @keyup.enter="handleSearch"
                                    ref="searchInput"
                                >
                                <button @click="handleSearch" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-navy-700 transition">
                                    <Icon icon="mdi:magnify" class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Search (mobile) -->
                    <button
                        @click="searchOpen = !searchOpen"
                        class="lg:hidden flex items-center justify-center w-9 h-9 rounded-full border border-gray-200 hover:bg-gray-50 transition text-gray-600"
                    >
                        <Icon icon="mdi:magnify" class="w-4 h-4" />
                    </button>

                    <!-- Kontakt (mobile dropdown) -->
                    <div class="relative lg:hidden" ref="contactRef">
                        <button
                            @click="contactOpen = !contactOpen"
                            class="flex items-center justify-center w-9 h-9 rounded-full border border-gray-200 hover:bg-gray-50 transition text-gray-600"
                        >
                            <Icon icon="fa6-solid:phone-volume" class="w-3.5 h-3.5" />
                        </button>
                        <div
                            v-if="contactOpen"
                            class="absolute right-0 top-full mt-2 w-56 bg-white rounded-xl border border-gray-100 shadow-xl py-2 z-50"
                        >
                            <a
                                :href="`tel:${settings.phone || '+998712345678'}`"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition"
                            >
                                <Icon icon="fa6-solid:phone-volume" class="w-4 h-4 text-navy-700" />
                                {{ settings.phone || '+998 71 234 56 78' }}
                            </a>
                            <a
                                :href="`mailto:${settings.email || 'info@yangiasr.uz'}`"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition"
                            >
                                <Icon icon="mdi:email-outline" class="w-4 h-4 text-navy-700" />
                                {{ settings.email || 'info@yangiasr.uz' }}
                            </a>
                        </div>
                    </div>

                    <!-- Kabinet (login) -->
                    <div class="relative" ref="userRef">
                        <button
                            @click="userOpen = !userOpen"
                            title="Kabinet"
                            class="flex items-center justify-center w-9 h-9 lg:w-10 lg:h-10 rounded-full border border-gray-200 hover:bg-gray-50 transition text-gray-600"
                        >
                            <Icon icon="mdi:account-circle-outline" class="w-4 h-4" />
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
                                <Icon icon="mdi:account-school-outline" class="w-4 h-4 text-navy-700" />
                                Talaba / xodim sifatida kirish
                            </Link>
                            <Link
                                href="/cabinet/login"
                                @click="userOpen = false"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition"
                            >
                                <Icon icon="mdi:file-document-edit-outline" class="w-4 h-4 text-navy-700" />
                                Qabul testiga kirish
                            </Link>
                            <Link
                                href="/qabul/ariza"
                                @click="userOpen = false"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-600 hover:bg-gray-50 transition"
                            >
                                <Icon icon="mdi:school-outline" class="w-4 h-4 text-navy-700" />
                                Ariza topshirish
                            </Link>
                        </div>
                    </div>

                    <!-- Qabul -->
                    <Link
                        href="/qabul/ariza"
                        class="hidden md:flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-brand-600 to-brand-700 text-white text-sm font-semibold rounded-full hover:shadow-lg hover:shadow-brand-600/30 transition-all"
                    >
                        Qabul 2026
                        <Icon icon="mdi:arrow-right" class="w-4 h-4" />
                    </Link>

                    <!-- Mobile burger -->
                    <button
                        @click="$emit('toggleMobile')"
                        class="lg:hidden flex items-center justify-center w-9 h-9 rounded-full border border-gray-200 hover:bg-gray-50 transition"
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
                        class="w-full pl-4 pr-10 py-2.5 bg-gray-50 border border-gray-200 rounded-xl text-sm focus:outline-none focus:border-navy-500 transition"
                        @keyup.enter="handleSearch"
                    >
                    <button @click="handleSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <Icon icon="mdi:magnify" class="w-5 h-5" />
                    </button>
                </div>
            </div>

        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { menuItems } from '@/Data/menuItems.js'

defineProps({
    settings:    { type: Object, default: () => ({}) },
    currentLang: { type: String, default: 'uz' },
    mobileOpen:  { type: Boolean, default: false },
})

const emit = defineEmits(['toggleMobile', 'changeLang'])

const page = usePage()

const searchQuery  = ref('')
const searchOpen   = ref(false)
const contactOpen  = ref(false)
const userOpen     = ref(false)
const scrolled     = ref(false)
const searchRef    = ref(null)
const contactRef   = ref(null)
const userRef      = ref(null)

const isActive = (href) => href && page.url === href

const handleSearch = () => {
    if (searchQuery.value.trim()) {
        window.location.href = `/qidiruv?q=${encodeURIComponent(searchQuery.value)}`
    }
}

const handleScroll = () => { scrolled.value = window.scrollY > 10 }

const handleOutsideClick = (e) => {
    if (searchRef.value && !searchRef.value.contains(e.target)) searchOpen.value = false
    if (contactRef.value && !contactRef.value.contains(e.target)) contactOpen.value = false
    if (userRef.value && !userRef.value.contains(e.target)) userOpen.value = false
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll)
    document.addEventListener('mousedown', handleOutsideClick)
})
onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll)
    document.removeEventListener('mousedown', handleOutsideClick)
})
</script>
