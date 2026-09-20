<template>
    <div class="space-y-6">

        <!-- Statistika -->
        <div class="rounded-2xl bg-navy-50 p-6 space-y-5">
            <div v-for="stat in stats" :key="stat.label" class="flex items-center gap-4">
                <span class="flex-shrink-0 w-12 h-12 rounded-full bg-white flex items-center justify-center shadow-sm">
                    <Icon :icon="stat.icon" class="w-6 h-6 text-brand-600" />
                </span>
                <div>
                    <p class="text-navy-400 text-sm leading-tight">{{ stat.label }}</p>
                    <p class="text-brand-900 font-bold text-xl leading-tight">{{ stat.value }}</p>
                </div>
            </div>
        </div>

        <!-- Qabul CTA — MUHIM: fon surati vaqtinchalik /public/blogs/blog-1.jpg -->
        <div class="rounded-2xl text-white relative overflow-hidden" style="min-height: 230px">
            <img src="/blogs/blog-1.jpg" alt="" class="absolute inset-0 w-full h-full object-cover">
            <div
                class="absolute inset-0"
                style="background: linear-gradient(90deg, rgba(67,56,202,0.95) 0%, rgba(67,56,202,0.85) 35%, rgba(67,56,202,0.45) 65%, rgba(67,56,202,0.08) 100%);"
            ></div>
            <div class="relative p-6">
                <h3 class="font-bold text-xl mb-2">Qabul 2025</h3>
                <p class="font-semibold text-sm mb-2 leading-snug">O'zingizning kelajagingizni bugundan boshlang!</p>
                <p class="text-white/80 text-sm leading-relaxed mb-5">
                    Bakalavriat, magistratura va qayta tayyorlash kurslariga qabul davom etmoqda.
                </p>
                <Link href="/qabul/ariza" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-full bg-white text-brand-700 text-sm font-semibold hover:bg-brand-50 transition">
                    Ariza topshirish
                    <Icon icon="mdi:arrow-right" class="w-4 h-4" />
                </Link>
            </div>
        </div>

        <!-- Tezkor havolalar -->
        <div class="rounded-2xl bg-white border-2 border-gray-100 p-6">
            <h3 class="font-bold text-navy-900 text-base mb-3">Tezkor havolalar</h3>
            <ul class="divide-y divide-gray-50">
                <li v-for="link in quickLinksSidebar" :key="link.label">
                    <Link :href="link.href" class="flex items-center justify-between py-3 text-sm text-navy-400 hover:text-brand-600 transition group">
                        <span class="flex items-center gap-2.5">
                            <Icon :icon="link.icon" class="w-4 h-4 text-brand-500" />
                            {{ link.label }}
                        </span>
                        <Icon icon="mdi:chevron-right" class="w-4 h-4 text-gray-300 group-hover:text-brand-500 group-hover:translate-x-0.5 transition-all" />
                    </Link>
                </li>
            </ul>
        </div>

        <!-- CTA kartalar — MUHIM: fon suratlari vaqtinchalik /public/blogs/blog-*.jpg -->
        <Link
            v-for="cta in ctaCards"
            :key="cta.title"
            :href="cta.href"
            class="cta-card relative block rounded-2xl overflow-hidden text-white group"
            style="min-height: 190px"
        >
            <img :src="cta.image" alt="" class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
            <div class="absolute inset-0" :style="{ background: cta.gradient }"></div>
            <div class="relative h-full flex flex-col justify-end p-6">
                <h4 class="font-bold text-lg leading-snug mb-2">{{ cta.title }}</h4>
                <p class="text-white/80 text-sm leading-snug mb-3">{{ cta.description }}</p>
                <span class="inline-flex items-center gap-1 text-sm font-semibold w-max px-3.5 py-2 rounded-full bg-white/15 group-hover:bg-white/25 transition">
                    {{ cta.buttonLabel }}
                    <Icon icon="mdi:arrow-right" class="w-4 h-4" />
                </span>
            </div>
        </Link>

    </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

// MUHIM: bu yon panel QuickLinks tagidan Hamkorlar (Partners) bo'limigacha
// bo'lgan butun asosiy kontent (Bizning tariximiz, So'nggi yangiliklar,
// Fakultetlar, Tadbirlar/Yangiliklar) bilan bir qatorda, yagona rightbar
// sifatida ko'rinadi (Home.vue'dagi umumiy grid orqali).
const stats = [
    { label: 'Talaba soni',              value: '3 000+',     icon: 'mdi:account-group-outline' },
    { label: "Professor-o'qituvchilar",  value: '200+',       icon: 'mdi:school-outline' },
    { label: "Ta'lim yo'nalishlari",     value: '100+',       icon: 'mdi:book-open-outline' },
    { label: 'Kampus hududi',            value: '50+ gektar', icon: 'mdi:map-marker-radius-outline' },
]

const quickLinksSidebar = [
    { label: "Elektron ta'lim tizimi",  icon: 'mdi:monitor-account',        href: '#' },
    { label: 'Talaba uchun xizmatlar',  icon: 'mdi:account-school-outline', href: '/talabalar/bakalavr/yoriqnoma' },
    { label: 'Kutubxona',               icon: 'mdi:bookshelf',              href: '/kutubxona' },
    { label: 'Rektorat',                icon: 'mdi:account-tie-outline',    href: '/tuzilma/rektor' },
    { label: 'Yotoqxona',               icon: 'mdi:home-city-outline',      href: '/talabalar/bakalavr/yotoqxona' },
    { label: 'SAVOL-JAVOB (FAQ)',       icon: 'mdi:help-circle-outline',    href: '/qabul/eslatma' },
]

const ctaCards = [
    {
        title: "Biz bilan birga o'sing!",
        description: 'Sizning bilimli va muvaffaqiyatli kelajagingiz uchun.',
        buttonLabel: 'Talabalar haqida',
        href: '/talabalar/bakalavr/yoriqnoma',
        image: '/blogs/blog-2.jpg',
        gradient: 'linear-gradient(90deg, rgba(79,70,229,0.95) 0%, rgba(79,70,229,0.82) 35%, rgba(79,70,229,0.4) 65%, rgba(79,70,229,0.05) 100%)',
    },
    {
        title: 'Kelajagingizni birga quramiz!',
        description: "Yangi Asr Universitetida o'qish — bu nafaqat ta'lim, balki hayotiy tayyorgarlik.",
        buttonLabel: 'Qabul 2025',
        href: '/qabul/ariza',
        image: '/blogs/blog-1.jpg',
        gradient: 'linear-gradient(90deg, rgba(11,19,48,0.95) 0%, rgba(11,19,48,0.82) 35%, rgba(11,19,48,0.4) 65%, rgba(11,19,48,0.05) 100%)',
    },
]
</script>

<style scoped>
.cta-card { box-shadow: 0 4px 16px rgba(0,0,0,0.12); }
</style>
