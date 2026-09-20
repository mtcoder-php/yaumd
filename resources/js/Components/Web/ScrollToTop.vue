<template>
    <Transition
        enter-active-class="transition duration-300 ease-out"
        enter-from-class="opacity-0 scale-50 translate-y-3"
        enter-to-class="opacity-100 scale-100 translate-y-0"
        leave-active-class="transition duration-200 ease-in"
        leave-from-class="opacity-100 scale-100 translate-y-0"
        leave-to-class="opacity-0 scale-50 translate-y-3"
    >
        <button
            v-if="visible"
            type="button"
            @click="scrollToTop"
            title="Sahifa boshiga qaytish"
            class="scroll-top-btn fixed right-4 bottom-4 md:right-8 md:bottom-8 z-40 flex items-center justify-center w-11 h-11 md:w-14 md:h-14 rounded-full group"
        >
            <!-- Scroll progressi — foydalanuvchi sahifani qancha o'qib bo'lganini ko'rsatadi -->
            <svg class="absolute inset-0 w-full h-full -rotate-90" viewBox="0 0 56 56">
                <circle cx="28" cy="28" r="25" fill="none" stroke-width="3" class="text-navy-100" stroke="currentColor" />
                <circle
                    cx="28" cy="28" r="25" fill="none" stroke-width="3" stroke-linecap="round"
                    class="text-brand-600 transition-[stroke-dashoffset] duration-150 ease-out"
                    stroke="currentColor"
                    :stroke-dasharray="circumference"
                    :stroke-dashoffset="circumference - (progress / 100) * circumference"
                />
            </svg>

            <!-- Markaziy tugma -->
            <span class="relative flex items-center justify-center w-9 h-9 md:w-10 md:h-10 rounded-full bg-gradient-to-br from-brand-600 to-brand-700 shadow-lg shadow-brand-600/30 group-hover:shadow-brand-600/50 transition-all duration-300 group-hover:-translate-y-0.5">
                <Icon icon="mdi:arrow-up" class="w-5 h-5 text-white" />
            </span>
        </button>
    </Transition>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import { Icon } from '@iconify/vue'

const visible  = ref(false)
const progress = ref(0)

const radius = 25
const circumference = 2 * Math.PI * radius

// MUHIM: chegara — shuncha piksel pastga tushilgandan keyingina tugma
// ko'rinadi, sahifa boshida ekranni band qilib turmaydi.
const SHOW_THRESHOLD = 320

let ticking = false

const updateScroll = () => {
    const scrollTop = window.scrollY || document.documentElement.scrollTop
    const docHeight = document.documentElement.scrollHeight - window.innerHeight

    visible.value = scrollTop > SHOW_THRESHOLD
    progress.value = docHeight > 0 ? Math.min(100, (scrollTop / docHeight) * 100) : 0
    ticking = false
}

const onScroll = () => {
    if (!ticking) {
        window.requestAnimationFrame(updateScroll)
        ticking = true
    }
}

const scrollToTop = () => {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    window.scrollTo({ top: 0, behavior: reduceMotion ? 'auto' : 'smooth' })
}

onMounted(() => {
    updateScroll()
    window.addEventListener('scroll', onScroll, { passive: true })
    window.addEventListener('resize', onScroll)
})
onBeforeUnmount(() => {
    window.removeEventListener('scroll', onScroll)
    window.removeEventListener('resize', onScroll)
})
</script>

<style scoped>
/* MUHIM: "rounded-full" faqat KO'RINISHNI doira qiladi — tugmaning
   bosiladigan hududi haqiqatda kvadrat bo'lib qoladi. Mobil ekranda bu
   tugma ko'pincha yangiliklar/kafedralar kartochkalarining pastki-o'ng
   burchagi ustiga to'g'ri keladi, shuning uchun ko'zga ko'rinmas kvadrat
   burchaklar orqa fondagi kartochka havolasiga tegishli bosishni "o'g'irlab"
   ketishi mumkin edi (foydalanuvchi kartochkani ochmoqchi bo'lsa ham,
   sahifa yuqoriga scroll bo'lib qolardi). clip-path bilan bosish hududi ham
   aynan ko'rinayotgan doiraga tenglashtiriladi — doiradan tashqari joyga
   bosish endi ostidagi kontentga o'tadi. */
.scroll-top-btn {
    clip-path: circle(50%);
    -webkit-clip-path: circle(50%);
}
</style>
