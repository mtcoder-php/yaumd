<template>
    <div>
        <!-- Desktop Navigation -->
        <div
            class="hidden lg:block bg-white border-b border-gray-100 sticky top-0 z-40 transition-shadow"
            :class="scrolled ? 'shadow-md' : ''"
        >
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between h-12">

                    <!-- Chap: Ta'lim dasturlari tugmasi -->
                    <div class="relative" ref="categoryRef">
                        <button
                            @click="categoryOpen = !categoryOpen"
                            class="flex items-center gap-2 px-4 h-12 text-sm font-semibold text-white bg-gradient-to-r from-[#0f3460] to-[#533483] hover:shadow-lg transition-all"
                        >
                            <Icon icon="mdi:menu" class="w-5 h-5" />
                            Ta'lim dasturlari
                            <Icon icon="mdi:chevron-down" class="w-4 h-4 transition-transform ml-1" :class="categoryOpen ? 'rotate-180' : ''" />
                        </button>

                        <!-- Category Dropdown -->
                        <div
                            v-if="categoryOpen"
                            class="absolute top-full left-0 w-72 bg-white border border-gray-100 shadow-2xl rounded-b-xl z-50 max-h-[80vh] overflow-y-auto"
                        >
                            <div
                                v-for="(cat, ci) in categories"
                                :key="ci"
                                class="border-b border-gray-50 last:border-0"
                            >
                                <button
                                    @click="toggleCategory(ci)"
                                    class="w-full flex items-center justify-between px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 transition"
                                >
                                    {{ cat.title }}
                                    <Icon
                                        icon="mdi:chevron-down"
                                        class="w-4 h-4 text-gray-400 transition-transform"
                                        :class="openCategory === ci ? 'rotate-180' : ''"
                                    />
                                </button>
                                <div v-if="openCategory === ci" class="bg-gray-50 border-t border-gray-100">
                                    <Link
                                        v-for="(item, ii) in cat.items"
                                        :key="ii"
                                        :href="item.path"
                                        @click="categoryOpen = false"
                                        class="flex items-center gap-2 px-6 py-2.5 text-sm text-gray-600 hover:text-[#0f3460] hover:bg-gray-100 transition"
                                    >
                                        <Icon icon="mdi:chevron-right" class="w-3.5 h-3.5 text-gray-400" />
                                        {{ item.title }}
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- O'rta: Mega menyu -->
                    <nav class="flex items-center h-full flex-1 px-4">
                        <div
                            v-for="item in menuItems"
                            :key="item.path"
                            class="relative h-full flex items-center group"
                        >
                            <Link
                                :href="item.href || '#'"
                                class="flex items-center gap-1 px-3 h-full text-sm font-medium text-gray-600 hover:text-[#0f3460] transition border-b-2 border-transparent hover:border-[#0f3460] whitespace-nowrap"
                                :class="isActive(item.href) ? 'text-[#0f3460] border-[#0f3460]' : ''"
                            >
                                {{ item.name }}
                                <Icon
                                    v-if="item.submenu"
                                    icon="mdi:chevron-down"
                                    class="w-3.5 h-3.5 transition-transform group-hover:rotate-180"
                                />
                            </Link>

                            <!-- Mega Dropdown -->
                            <div
                                v-if="item.submenu"
                                class="absolute top-full left-0 bg-white border border-gray-100 shadow-2xl rounded-b-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50"
                                style="min-width: 220px"
                            >
                                <ul class="py-2">
                                    <li
                                        v-for="sub in item.submenu"
                                        :key="sub.path"
                                        class="relative group/sub"
                                    >
                                        <!-- Inner submenu bor -->
                                        <div
                                            v-if="sub.innersubmenu"
                                            class="flex items-center justify-between px-4 py-2.5 text-sm text-gray-600 hover:text-[#0f3460] hover:bg-blue-50 transition cursor-default group/sub"
                                        >
                                            <span>{{ sub.title }}</span>
                                            <Icon icon="mdi:chevron-right" class="w-4 h-4 text-gray-400 flex-shrink-0" />

                                            <!-- Inner submenu -->
                                            <div
                                                class="absolute left-full top-0 bg-white border border-gray-100 shadow-2xl rounded-xl py-2 opacity-0 invisible group-hover/sub:opacity-100 group-hover/sub:visible transition-all duration-200 z-50"
                                                style="min-width: 260px; margin-left: 1px"
                                            >
                                                <div class="px-4 py-2 border-b border-gray-100 mb-1">
                                                    <p class="text-xs font-semibold text-[#0f3460] uppercase tracking-wide">
                                                        {{ sub.title }}
                                                    </p>
                                                </div>
                                                <Link
                                                    v-for="inner in sub.innersubmenu"
                                                    :key="inner.path"
                                                    :href="inner.path"
                                                    class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-600 hover:text-[#0f3460] hover:bg-blue-50 transition"
                                                >
                                                    <Icon icon="mdi:chevron-right" class="w-3.5 h-3.5 text-gray-300" />
                                                    {{ inner.title }}
                                                </Link>
                                            </div>
                                        </div>

                                        <!-- Oddiy link -->
                                        <Link
                                            v-else
                                            :href="sub.path"
                                            class="flex items-center gap-2 px-4 py-2.5 text-sm text-gray-600 hover:text-[#0f3460] hover:bg-blue-50 transition"
                                        >
                                            <Icon icon="mdi:chevron-right" class="w-3.5 h-3.5 text-gray-300" />
                                            {{ sub.title }}
                                        </Link>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </nav>

                    <!-- O'ng: Shiori -->
                    <div class="flex items-center gap-2 text-sm text-gray-500 flex-shrink-0">
                        <Icon icon="mdi:school" class="w-5 h-5 text-[#0f3460]" />
                        <span class="font-medium italic">Bilim — kelajak podevori</span>
                    </div>

                </div>
            </div>
        </div>

        <!-- Mobile Drawer -->
        <Transition
            enter-active-class="transition duration-300"
            enter-from-class="-translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition duration-200"
            leave-from-class="translate-x-0"
            leave-to-class="-translate-x-full"
        >
            <div
                v-if="mobileOpen"
                class="lg:hidden fixed inset-y-0 left-0 w-80 bg-white shadow-2xl z-50 flex flex-col"
            >
                <!-- Drawer header -->
                <div class="flex items-center justify-between px-4 py-4 border-b border-gray-100 bg-gradient-to-r from-[#0f3460] to-[#533483]">
                    <div class="flex items-center gap-2 text-white">
                        <Icon icon="mdi:menu" class="w-5 h-5" />
                        <span class="font-semibold text-sm">Menyu</span>
                    </div>
                    <button
                        @click="$emit('closeMobile')"
                        class="text-white/80 hover:text-white transition"
                    >
                        <Icon icon="mdi:close" class="w-5 h-5" />
                    </button>
                </div>

                <!-- Qabul tugmasi -->
                <div class="px-4 py-3 bg-blue-50 border-b border-blue-100">
                    <Link
                        href="/qabul/ariza"
                        @click="$emit('closeMobile')"
                        class="flex items-center justify-center gap-2 w-full py-2.5 bg-gradient-to-r from-[#0f3460] to-[#533483] text-white text-sm font-medium rounded-xl"
                    >
                        <Icon icon="mdi:school-outline" class="w-4 h-4" />
                        Qabul 2025 — Ariza topshirish
                    </Link>
                </div>

                <!-- Menyu items -->
                <div class="flex-1 overflow-y-auto">
                    <div
                        v-for="item in menuItems"
                        :key="item.path"
                        class="border-b border-gray-50"
                    >
                        <!-- Asosiy item -->
                        <button
                            @click="toggleMobileItem(item.path)"
                            class="w-full flex items-center justify-between px-4 py-3.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#0f3460] transition"
                        >
                            <span>{{ item.name }}</span>
                            <Icon
                                v-if="item.submenu"
                                icon="mdi:chevron-down"
                                class="w-4 h-4 text-gray-400 transition-transform"
                                :class="mobileExpanded === item.path ? 'rotate-180' : ''"
                            />
                        </button>

                        <!-- Submenu -->
                        <div v-if="item.submenu && mobileExpanded === item.path" class="bg-gray-50">
                            <div
                                v-for="sub in item.submenu"
                                :key="sub.path"
                                class="border-t border-gray-100"
                            >
                                <!-- Inner submenu bor -->
                                <div v-if="sub.innersubmenu">
                                    <button
                                        @click="toggleMobileSub(sub.path)"
                                        class="w-full flex items-center justify-between px-6 py-3 text-sm font-medium text-gray-600 hover:text-[#0f3460] transition"
                                    >
                                        <span>{{ sub.title }}</span>
                                        <Icon
                                            icon="mdi:chevron-down"
                                            class="w-3.5 h-3.5 text-gray-400 transition-transform"
                                            :class="mobileSubExpanded === sub.path ? 'rotate-180' : ''"
                                        />
                                    </button>
                                    <div v-if="mobileSubExpanded === sub.path" class="bg-white">
                                        <Link
                                            v-for="inner in sub.innersubmenu"
                                            :key="inner.path"
                                            :href="inner.path"
                                            @click="$emit('closeMobile')"
                                            class="flex items-center gap-2 px-8 py-2.5 text-sm text-gray-500 hover:text-[#0f3460] hover:bg-blue-50 transition border-t border-gray-50"
                                        >
                                            <Icon icon="mdi:circle-small" class="w-4 h-4 text-gray-300" />
                                            {{ inner.title }}
                                        </Link>
                                    </div>
                                </div>

                                <!-- Oddiy link -->
                                <Link
                                    v-else
                                    :href="sub.path"
                                    @click="$emit('closeMobile')"
                                    class="flex items-center gap-2 px-6 py-3 text-sm text-gray-600 hover:text-[#0f3460] hover:bg-blue-50 transition"
                                >
                                    <Icon icon="mdi:chevron-right" class="w-3.5 h-3.5 text-gray-300" />
                                    {{ sub.title }}
                                </Link>
                            </div>
                        </div>
                    </div>

                    <!-- Ta'lim dasturlari -->
                    <div class="border-b border-gray-50">
                        <button
                            @click="toggleMobileItem('categories')"
                            class="w-full flex items-center justify-between px-4 py-3.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 hover:text-[#0f3460] transition"
                        >
                            <span class="flex items-center gap-2">
                                <Icon icon="mdi:school" class="w-4 h-4 text-[#0f3460]" />
                                Ta'lim dasturlari
                            </span>
                            <Icon
                                icon="mdi:chevron-down"
                                class="w-4 h-4 text-gray-400 transition-transform"
                                :class="mobileExpanded === 'categories' ? 'rotate-180' : ''"
                            />
                        </button>
                        <div v-if="mobileExpanded === 'categories'" class="bg-gray-50">
                            <div
                                v-for="(cat, ci) in categories"
                                :key="ci"
                                class="border-t border-gray-100"
                            >
                                <button
                                    @click="toggleMobileCat(ci)"
                                    class="w-full flex items-center justify-between px-6 py-3 text-sm font-medium text-gray-600 hover:text-[#0f3460] transition"
                                >
                                    {{ cat.title }}
                                    <Icon
                                        icon="mdi:chevron-down"
                                        class="w-3.5 h-3.5 text-gray-400 transition-transform"
                                        :class="mobileCatExpanded === ci ? 'rotate-180' : ''"
                                    />
                                </button>
                                <div v-if="mobileCatExpanded === ci" class="bg-white">
                                    <Link
                                        v-for="(item, ii) in cat.items"
                                        :key="ii"
                                        :href="item.path"
                                        @click="$emit('closeMobile')"
                                        class="flex items-center gap-2 px-8 py-2.5 text-sm text-gray-500 hover:text-[#0f3460] hover:bg-blue-50 transition border-t border-gray-50"
                                    >
                                        <Icon icon="mdi:circle-small" class="w-4 h-4 text-gray-300" />
                                        {{ item.title }}
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
                    <p class="text-xs text-gray-400 text-center">© 2025 Yangi Asr Universiteti</p>
                </div>
            </div>
        </Transition>

        <!-- Overlay -->
        <Transition
            enter-active-class="transition duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="mobileOpen"
                @click="$emit('closeMobile')"
                class="lg:hidden fixed inset-0 bg-black/40 z-40"
            />
        </Transition>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { menuItems } from '@/Data/menuItems.js'

defineProps({
    mobileOpen: { type: Boolean, default: false },
})

defineEmits(['closeMobile'])

const page            = usePage()
const scrolled        = ref(false)
const categoryOpen    = ref(false)
const openCategory    = ref(null)
const mobileExpanded  = ref(null)
const mobileSubExpanded = ref(null)
const mobileCatExpanded = ref(null)
const categoryRef     = ref(null)

// Scroll
const handleScroll = () => { scrolled.value = window.scrollY > 10 }
onMounted(() => window.addEventListener('scroll', handleScroll))
onUnmounted(() => window.removeEventListener('scroll', handleScroll))

// Outside click — category yopish
const handleOutside = (e) => {
    if (categoryRef.value && !categoryRef.value.contains(e.target)) {
        categoryOpen.value = false
    }
}
onMounted(() => document.addEventListener('mousedown', handleOutside))
onUnmounted(() => document.removeEventListener('mousedown', handleOutside))

const isActive = (href) => href && page.url === href

// Category accordion
const toggleCategory = (i) => {
    openCategory.value = openCategory.value === i ? null : i
}

// Mobile accordion
const toggleMobileItem = (path) => {
    mobileExpanded.value = mobileExpanded.value === path ? null : path
    mobileSubExpanded.value = null
    mobileCatExpanded.value = null
}

const toggleMobileSub = (path) => {
    mobileSubExpanded.value = mobileSubExpanded.value === path ? null : path
}

const toggleMobileCat = (i) => {
    mobileCatExpanded.value = mobileCatExpanded.value === i ? null : i
}

// Ta'lim dasturlari (Category panel)
const categories = [
    {
        title: "Mumtoz sharq filologiyasi",
        items: [
            { title: "Arab tili filologiyasi", path: "/yonalishlar/arab-tili" },
            { title: "Sharq mumtoz tili (Arab tili)", path: "/yonalishlar/sharq-mumtoz" },
            { title: "Lingvistika: Arab tili (Magistr)", path: "/yonalishlar/lingvistika-arab" },
        ]
    },
    {
        title: "Filologiya va tillarni o'qitish",
        items: [
            { title: "Ingliz tili", path: "/yonalishlar/ingliz-tili" },
            { title: "Rus tili", path: "/yonalishlar/rus-tili" },
            { title: "Xitoy tili", path: "/yonalishlar/xitoy-tili" },
            { title: "Turk tili", path: "/yonalishlar/turk-tili" },
            { title: "Koreys tili", path: "/yonalishlar/koreys-tili" },
            { title: "Lingvistika: Ingliz tili (Magistr)", path: "/yonalishlar/lingvistika-ingliz" },
        ]
    },
    {
        title: "Maxsus pedagogika",
        items: [
            { title: "Logopediya", path: "/yonalishlar/logopediya" },
            { title: "Psixologiya", path: "/yonalishlar/psixologiya" },
        ]
    },
    {
        title: "Maktab va maktabgacha ta'lim",
        items: [
            { title: "Ta'lim va tarbiya nazariyasi", path: "/yonalishlar/talim-tarbiya" },
            { title: "Maktabgacha ta'lim", path: "/yonalishlar/maktabgacha" },
        ]
    },
    {
        title: "Umumta'lim fanlari",
        items: [
            { title: "Pedagogika", path: "/yonalishlar/pedagogika" },
            { title: "Dizayn: Interyerni loyihalash", path: "/yonalishlar/dizayn-interior" },
            { title: "Dizayn: Liboslar dizayni", path: "/yonalishlar/dizayn-libos" },
            { title: "Iqtisodiyot", path: "/yonalishlar/iqtisodiyot" },
            { title: "Buxgalteriya hisobi", path: "/yonalishlar/buxgalteriya" },
            { title: "Dasturiy injiniring", path: "/yonalishlar/dasturiy-injiniring" },
            { title: "Matematika", path: "/yonalishlar/matematika" },
            { title: "Tarix", path: "/yonalishlar/tarix" },
        ]
    },
]
</script>
