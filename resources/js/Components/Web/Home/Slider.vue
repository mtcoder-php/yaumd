<template>
    <section
        class="relative bg-navy-900 overflow-hidden"
        @mouseenter="pause = true"
        @mouseleave="pause = false"
    >
        <!-- Slaydlar (o'zimiz yozgan, Swiper'siz — crossfade) -->
        <div class="relative h-[420px] md:h-[520px]">
            <Transition
                v-for="(slide, i) in displaySlides"
                :key="i"
                name="fade"
            >
                <div v-show="i === activeIndex" class="absolute inset-0">
                    <img
                        v-if="slide.image"
                        :src="slide.image"
                        :alt="slide.title"
                        class="absolute inset-0 w-full h-full object-cover"
                        draggable="false"
                    >
                    <div
                        v-else
                        class="absolute inset-0 flex items-center justify-center"
                        style="background: linear-gradient(120deg, var(--color-navy-900), var(--color-navy-700) 55%, var(--color-brand-700));"
                    >
                        <Icon icon="mdi:office-building-outline" class="w-32 h-32 text-white/10" />
                    </div>
                    <div class="absolute inset-0 hero-overlay"></div>
                </div>
            </Transition>
        </div>

        <!-- Doimiy matn qatlami — slayd fon rasmi almashsa ham matn joyida qoladi -->
        <div class="absolute inset-0 flex items-center pointer-events-none">
            <div class="container mx-auto px-4">
                <div class="max-w-xl pointer-events-auto">
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-brand-600/25 border border-brand-400/30 text-brand-200 text-xs font-semibold mb-5 backdrop-blur-sm">
                        <Icon icon="mdi:sparkles-outline" class="w-3.5 h-3.5" />
                        Kelajak uchun bilim
                    </span>
                    <h1 class="text-3xl md:text-[2.6rem] font-extrabold leading-tight text-white mb-4">
                        Yangi asr universiteti —
                        <span class="block text-brand-300">bilim, innovatsiya, taraqqiyot!</span>
                    </h1>
                    <p class="text-white/70 text-sm md:text-base leading-relaxed mb-7 max-w-md">
                        Zamonaviy ta'lim, ilm-fan va innovatsiyalar orqali barqaror kelajakni birgalikda quramiz.
                    </p>
                    <div class="flex flex-wrap items-center gap-3">
                        <Link href="/universitet/haqida/umumiy" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold transition shadow-lg shadow-brand-900/30">
                            Universitet haqida
                            <Icon icon="mdi:arrow-right" class="w-4 h-4" />
                        </Link>
                        <Link href="/qabul/ariza" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-white/30 text-white text-sm font-semibold hover:bg-white/10 transition">
                            Qabul 2026
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Suzuvchi statistika kartasi -->
        <div class="hidden lg:block absolute right-8 xl:right-16 top-1/2 -translate-y-1/2 w-56 rounded-2xl bg-navy-800/90 border border-white/10 backdrop-blur-sm shadow-2xl p-5 z-10">
            <div
                v-for="(stat, i) in heroStats"
                :key="stat.label"
                class="flex items-center gap-3"
                :class="{ 'mb-4': i < heroStats.length - 1 }"
            >
                <span class="flex-shrink-0 w-9 h-9 rounded-lg bg-brand-600/20 flex items-center justify-center">
                    <Icon :icon="stat.icon" class="w-6 h-6 text-brand-300" />
                </span>
                <div>
                    <p class="text-white font-bold text-sm leading-tight">{{ stat.value }}</p>
                    <p class="text-white/50 text-[11px] leading-tight">{{ stat.label }}</p>
                </div>
            </div>
        </div>

        <!-- Navigatsiya strelkalari -->
        <button
            v-if="hasMultiple"
            @click="prev"
            class="absolute left-3 md:left-5 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-white/15 hover:bg-brand-600 backdrop-blur-sm flex items-center justify-center text-white transition"
        >
            <Icon icon="mdi:chevron-left" class="w-5 h-5" />
        </button>
        <button
            v-if="hasMultiple"
            @click="next"
            class="absolute right-3 md:right-5 top-1/2 -translate-y-1/2 z-10 w-10 h-10 rounded-full bg-white/15 hover:bg-brand-600 backdrop-blur-sm flex items-center justify-center text-white transition"
        >
            <Icon icon="mdi:chevron-right" class="w-5 h-5" />
        </button>

        <!-- Nuqta pagination -->
        <div v-if="hasMultiple" class="absolute left-6 md:left-10 bottom-5 z-10 flex items-center gap-1.5">
            <button
                v-for="(slide, i) in displaySlides"
                :key="i"
                @click="activeIndex = i"
                class="pagination-dot rounded-full transition-all duration-300"
                :class="i === activeIndex ? 'active' : ''"
            ></button>
        </div>

    </section>
</template>

<script setup>
import { computed, ref, onMounted, onUnmounted, watch } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

const props = defineProps({
    slides: { type: Array, default: () => [] },
    lang:   { type: String, default: 'uz' },
})

// MUHIM: DB'da hali "Slider" yozuvi (admin panel > Sliderlar) bo'lmasa,
// shu fayllar ko'rsatiladi — public/sliders/slide1-4.jpg. Xuddi shu nomlar
// bilan (slide1.jpg, slide2.jpg, ...) haqiqiy universitet suratlarini
// almashtirib qo'ysangiz, kodni o'zgartirmasdan to'g'ridan-to'g'ri ishlaydi.
// Agar fayl topilmasa, gradient fonga o'tadi.
const demoSlides = [
    { title: 'Yangi Asr Universiteti', image: '/sliders/slide1.png' },
    { title: 'Yangi Asr Universiteti', image: '/sliders/slide2.png' },
    { title: 'Yangi Asr Universiteti', image: '/sliders/slide3.png' },
]

const displaySlides = computed(() => {
    if (!props.slides?.length) return demoSlides
    return props.slides.map(s => ({
        title: s[`title_${props.lang}`] || s.title_uz || 'Yangi Asr Universiteti',
        image: s.image,
    }))
})

const hasMultiple = computed(() => displaySlides.value.length > 1)

const activeIndex = ref(0)
const pause = ref(false)
let timer = null

const next = () => {
    activeIndex.value = (activeIndex.value + 1) % displaySlides.value.length
}
const prev = () => {
    activeIndex.value = (activeIndex.value - 1 + displaySlides.value.length) % displaySlides.value.length
}

const startAutoplay = () => {
    stopAutoplay()
    if (!hasMultiple.value) return
    timer = setInterval(() => {
        if (!pause.value) next()
    }, 5000)
}
const stopAutoplay = () => {
    if (timer) clearInterval(timer)
    timer = null
}

onMounted(startAutoplay)
onUnmounted(stopAutoplay)
watch(() => displaySlides.value.length, () => {
    activeIndex.value = 0
    startAutoplay()
})

const heroStats = [
    { icon: 'mdi:calendar-blank-outline',   value: '2026',  label: "O'quv yili" },
    { icon: 'mdi:book-open-variant',        value: '30+',  label: "Ta'lim yo'nalishi" },
    { icon: 'mdi:account-group-outline',    value: '5000+', label: 'Talabalar' },
    { icon: 'mdi:account-tie-outline',      value: '200+',  label: "Professor-o'qituvchi" },
]
</script>

<style scoped>
.hero-overlay {
    background: linear-gradient(
        90deg,
        rgba(7, 12, 34, 0.92) 0%,
        rgba(7, 12, 34, 0.75) 32%,
        rgba(7, 12, 34, 0.25) 60%,
        rgba(7, 12, 34, 0.05) 100%
    );
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.6s ease;
}
.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.pagination-dot {
    width: 8px;
    height: 8px;
    background: rgba(255, 255, 255, 0.5);
}
.pagination-dot.active {
    background: var(--color-brand-400);
    width: 22px;
}
</style>
