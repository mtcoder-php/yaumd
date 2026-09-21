<template>
    <div class="space-y-10">

        <!-- Statistika qatori -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div
                v-for="stat in stats"
                :key="stat.label"
                class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 text-center transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
            >
                <div
                    class="w-12 h-12 mx-auto rounded-xl flex items-center justify-center mb-3"
                    :style="{ background: stat.bg }"
                >
                    <Icon :icon="stat.icon" class="w-6 h-6" :style="{ color: stat.color }" />
                </div>
                <p class="text-2xl font-bold text-navy-900">{{ stat.value }}</p>
                <p class="text-gray-500 text-xs mt-1">{{ stat.label }}</p>
            </div>
        </div>

        <!-- Video karta — MUHIM: vaqtinchalik surat /sliders/slide1.jpg
             ishlatilmoqda (Home/Mission.vue'dagi bilan bir xil, universitet
             mavzusiga aloqasi yo'q namuna surat). Haqiqiy "Universitet
             hayoti" video/surati tayyor bo'lgach shu manzil va pastdagi
             video modal ichidagi matn almashtirilishi kerak. -->
        <button
            type="button"
            @click="videoOpen = true"
            class="relative block w-full rounded-2xl overflow-hidden group"
            style="height: 260px"
        >
            <img
                src="/sliders/slide1.jpg"
                alt="Universitet hayoti"
                class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
            >
            <div
                class="absolute inset-0"
                style="background: linear-gradient(0deg, rgba(11,19,48,0.78) 0%, rgba(11,19,48,0.2) 45%, rgba(11,19,48,0.05) 100%);"
            ></div>

            <span class="absolute left-4 bottom-4 flex items-center gap-3">
                <span class="flex-shrink-0 w-11 h-11 rounded-full bg-brand-600 group-hover:bg-brand-500 flex items-center justify-center shadow-lg transition">
                    <Icon icon="mdi:play" class="w-5 h-5 text-white ml-0.5" />
                </span>
                <span class="text-left">
                    <span class="block text-white text-sm font-semibold leading-tight">Universitet hayoti</span>
                    <span class="block text-white/70 text-xs leading-tight mt-0.5">video tomosha qiling</span>
                </span>
            </span>

            <span class="absolute right-4 bottom-4 text-white/80 text-xs font-medium">03:24</span>
        </button>

        <!-- Asosiy yo'nalishlar — MUHIM: quyidagi 4 karta hozircha "#"
             manziliga ishora qiladi, chunki ularning haqiqiy sahifalari
             hali qurilmagan (bu "Universitet haqida" bo'limidan tashqari,
             "Faoliyat" bo'limiga tegishli bo'lishi mumkin). Ular tayyor
             bo'lgach, href qiymatlarini haqiqiy (inglizcha) route'larga
             almashtirish kerak. -->
        <div>
            <h2 class="text-xl font-bold text-navy-900 mb-5">Asosiy yo'nalishlar</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div
                    v-for="dir in directions"
                    :key="dir.title"
                    class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
                >
                    <div
                        class="w-12 h-12 rounded-xl flex items-center justify-center mb-4"
                        :style="{ background: dir.bg }"
                    >
                        <Icon :icon="dir.icon" class="w-6 h-6" :style="{ color: dir.color }" />
                    </div>
                    <h3 class="font-semibold text-navy-900 mb-2">{{ dir.title }}</h3>
                    <p class="text-gray-500 text-sm leading-relaxed mb-4">{{ dir.desc }}</p>
                    <Link
                        :href="dir.href"
                        class="inline-flex items-center gap-1.5 text-brand-600 text-sm font-semibold hover:gap-2.5 transition-all duration-300"
                    >
                        Batafsil
                        <Icon icon="mdi:arrow-right" class="w-4 h-4" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- CTA — "Qabul haqida" tugmasi hozircha mavjud bo'lgan yagona
             qabul manziliga (/qabul/ariza) yo'naltirilgan; agar buning
             o'rniga alohida "qabul haqida umumiy ma'lumot" sahifasi
             kerak bo'lsa (hali qurilmagan), ayting — shu manzilga
             almashtiraman. -->
        <div class="relative overflow-hidden rounded-2xl p-8 md:p-10" style="background: linear-gradient(135deg, var(--color-navy-700), var(--color-brand-700))">
            <div class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
                <span class="absolute -right-10 -top-14 w-56 h-56 rounded-full border-[3px] border-white/10"></span>
                <span class="absolute right-16 bottom-0 w-32 h-32 rounded-full bg-white/5"></span>
                <span class="absolute right-52 top-6 w-16 h-16 rounded-2xl bg-white/5 rotate-45"></span>
            </div>
            <div class="relative max-w-md">
                <h3 class="text-white text-xl md:text-2xl font-bold mb-3">Kechagi bilim — bugungi muvaffaqiyat!</h3>
                <p class="text-white/80 text-sm leading-relaxed mb-6">
                    Yangi Asr Universitetida bugun boshlangan ta'lim yo'li ertangi kasbiy muvaffaqiyatingiz uchun mustahkam poydevor bo'ladi.
                </p>
                <Link
                    href="/qabul/ariza"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-white text-navy-900 rounded-xl text-sm font-bold hover:bg-gray-50 transition"
                >
                    Qabul haqida
                    <Icon icon="mdi:arrow-right" class="w-4 h-4" />
                </Link>
            </div>
        </div>

        <!-- Video modal (Home/Mission.vue bilan bir xil naqsh) -->
        <div
            v-if="videoOpen"
            @click.self="videoOpen = false"
            class="fixed inset-0 z-[60] bg-navy-950/80 flex items-center justify-center p-4"
        >
            <div class="relative w-full max-w-2xl aspect-video bg-black rounded-xl overflow-hidden flex items-center justify-center">
                <button
                    type="button"
                    @click="videoOpen = false"
                    class="absolute top-3 right-3 w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition"
                >
                    <Icon icon="mdi:close" class="w-5 h-5" />
                </button>
                <p class="text-white/60 text-sm">Video tez orada qo'shiladi</p>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

const videoOpen = ref(false)

const stats = [
    { label: 'Fakultet',                     value: '5',     icon: 'mdi:office-building-outline',        color: '#3b82f6', bg: '#eff6ff' },
    { label: 'Kafedralar',                   value: '20+',   icon: 'mdi:bank-outline',                   color: '#22c55e', bg: '#f0fdf4' },
    { label: 'Talabalar',                    value: '3000+', icon: 'mdi:account-group-outline',          color: '#a855f7', bg: '#faf5ff' },
    { label: "Professor-o'qituvchilar",      value: '200+',  icon: 'mdi:school-outline',                 color: '#f97316', bg: '#fff7ed' },
]

const directions = [
    {
        title: "Ta'lim",
        desc: "Zamonaviy o'quv dasturlari va malakali professor-o'qituvchilar bilan sifatli ta'lim.",
        icon: 'mdi:book-open-page-variant-outline',
        color: '#4f46e5', bg: '#eef2ff',
        href: '#',
    },
    {
        title: 'Ilm-fan',
        desc: "Ilmiy-tadqiqot ishlari, ilmiy jamiyatlar va yosh olimlarni qo'llab-quvvatlash.",
        icon: 'mdi:flask-outline',
        color: '#0891b2', bg: '#ecfeff',
        href: '#',
    },
    {
        title: 'Xalqaro hamkorlik',
        desc: "Xorijiy universitetlar bilan almashinuv dasturlari va qo'shma loyihalar.",
        icon: 'mdi:earth',
        color: '#16255a', bg: '#eef1f8',
        href: '#',
    },
    {
        title: 'Rivojlanish',
        desc: "Infratuzilma, raqamlashtirish va talabalar uchun yangi imkoniyatlar yaratish.",
        icon: 'mdi:trending-up',
        color: '#d68a1f', bg: '#fff7e6',
        href: '#',
    },
]
</script>
