<template>
    <section class="py-10 bg-white">
        <div class="container mx-auto px-4">
            <div class=" bg-white overflow-hidden">

                <!-- Sarlavha -->
                <div class="px-5 pt-5 pb-4">
                    <h3 class="flex items-center gap-2 text-2xl font-bold text-navy-900">
                        <Icon icon="mdi:handshake-outline" class="w-6 h-6 text-brand-600" />
                        Hamkorlarimiz
                    </h3>
                </div>

                <!-- Uzluksiz oqib turuvchi qator (marquee) — itemlar birma-bir
                     kirib, birma-bir chiqib turadi, sahifama-sahifa sakramaydi.
                     Ro'yxat 2 marta ketma-ket chiqariladi va -50% gacha silliq
                     siljitiladi — shu tufayli oxiri boshiga uzilishsiz ulanadi. -->
                <div class="relative py-6 overflow-hidden marquee-fade">
                    <div
                        class="marquee-track flex flex-nowrap items-center w-max"
                        :style="{ '--marquee-duration': marqueeDuration + 's' }"
                    >
                        <div class="flex flex-nowrap items-center divide-x divide-gray-100">
                            <a
                                v-for="partner in displayPartners"
                                :key="'a-' + partner.name"
                                :href="partner.url || '#'"
                                target="_blank"
                                class="flex items-center gap-2 px-4 sm:px-6 py-2 hover:opacity-70 transition"
                            >
                                <img v-if="partner.logo" :src="partner.logo" :alt="partner.name" class="h-7 w-auto max-w-[36px] object-contain flex-shrink-0">
                                <Icon v-else :icon="partner.icon || 'mdi:domain'" class="w-7 h-7 text-brand-600 flex-shrink-0" />
                                <span class="text-xs md:text-sm font-semibold text-navy-800 leading-snug whitespace-nowrap">{{ partner.name }}</span>
                            </a>
                        </div>
                        <!-- Xuddi shu ro'yxatning nusxasi — uzluksiz aylanish uchun -->
                        <div class="flex flex-nowrap items-center divide-x divide-gray-100" aria-hidden="true">
                            <a
                                v-for="partner in displayPartners"
                                :key="'b-' + partner.name"
                                :href="partner.url || '#'"
                                target="_blank"
                                tabindex="-1"
                                class="flex items-center gap-2 px-4 sm:px-6 py-2 hover:opacity-70 transition"
                            >
                                <img v-if="partner.logo" :src="partner.logo" :alt="partner.name" class="h-7 w-auto max-w-[36px] object-contain flex-shrink-0">
                                <Icon v-else :icon="partner.icon || 'mdi:domain'" class="w-7 h-7 text-brand-600 flex-shrink-0" />
                                <span class="text-xs md:text-sm font-semibold text-navy-800 leading-snug whitespace-nowrap">{{ partner.name }}</span>
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>
</template>

<script setup>
import { computed } from 'vue'
import { Icon } from '@iconify/vue'

const props = defineProps({
    partners: { type: Array, default: () => [] },
})

// MUHIM: haqiqiy hamkorlar DB'ga (admin panel > Hamkorlar) kiritilmaguncha,
// quyidagi NAMUNA (demo) ro'yxat ko'rsatiladi — logotip o'rniga vaqtinchalik
// ikonka ishlatilgan. Har bir hamkorning haqiqiy logotipi tayyor bo'lgach,
// backend'dan kelgan `partners` propi (rasm bilan) ustunlik qiladi.
const demoPartners = [
    { name: 'UNESCO', icon: 'mdi:bank-outline' },
    { name: 'THE WORLD BANK', icon: 'mdi:earth' },
    { name: 'Erasmus+', icon: 'mdi:flag-variant-outline' },
    { name: "O'zbekiston Respublikasi Innovatsiyalar vazirligi", icon: 'mdi:seal-variant' },
    { name: 'Mahalliy hamkorlar', icon: 'mdi:flower-tulip-outline' },
    { name: 'Xalqaro universitetlar', icon: 'mdi:account-school-outline' },
    { name: "Davlat ta'lim boshqarmalari", icon: 'mdi:office-building-outline' },
    { name: 'Ilmiy-tadqiqot markazlari', icon: 'mdi:flask-outline' },
    { name: 'Nodavlat notijorat tashkilotlar', icon: 'mdi:hand-heart-outline' },
    { name: 'Xorijiy elchixonalar', icon: 'mdi:passport' },
]

const displayPartners = computed(() => {
    if (props.partners?.length) {
        return props.partners.map(p => ({ name: p.name, logo: p.logo, url: p.url }))
    }
    return demoPartners
})

// MUHIM: tezlik hamkorlar soniga qarab moslashadi — item qancha ko'p bo'lsa,
// aylanish shuncha uzoqroq davom etadi, natijada oqim tezligi (piksel/soniya)
// doim bir xil bo'lib qoladi (ko'p bo'lsa ham, kam bo'lsa ham "shoshilib"
// yoki "sudralib" ketmaydi).
const marqueeDuration = computed(() => Math.max(displayPartners.value.length * 3, 12))
</script>

<style scoped>
.marquee-track {
    animation: partners-marquee var(--marquee-duration, 20s) linear infinite;
}
/* Sichqoncha ustiga kelganda oqim to'xtaydi — nom yoki logotipni o'qish qulay bo'lishi uchun */
.marquee-track:hover {
    animation-play-state: paused;
}
@keyframes partners-marquee {
    from { transform: translateX(0); }
    to   { transform: translateX(-50%); }
}
/* Chap va o'ng chetlarda yumshoq yo'qolish effekti */
.marquee-fade {
    -webkit-mask-image: linear-gradient(90deg, transparent 0, #000 48px, #000 calc(100% - 48px), transparent 100%);
    mask-image: linear-gradient(90deg, transparent 0, #000 48px, #000 calc(100% - 48px), transparent 100%);
}
@media (prefers-reduced-motion: reduce) {
    .marquee-track {
        animation: none;
    }
}
</style>
