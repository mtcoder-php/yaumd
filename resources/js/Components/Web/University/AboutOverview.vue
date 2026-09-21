<template>
    <div class="space-y-10">

        <!-- Umumiy ma'lumot: matn (chap) + video (o'ng) — namunadagi kabi
             video pastda emas, matn/statistika blokining O'NG tomonida,
             taxminan bir xil balandlikda joylashadi. -->
        <div class="flex flex-col lg:flex-row gap-6 lg:gap-8 items-stretch">

            <!-- Matn + statistika -->
            <div class="flex-1 min-w-0">
                <span class="inline-flex items-center gap-2 text-brand-600 text-xs font-bold uppercase tracking-wide mb-3">
                    <Icon icon="mdi:folder-outline" class="w-4 h-4" />
                    Umumiy ma'lumot
                </span>
                <h1 class="text-2xl font-bold text-navy-900 mb-3">Yangi Asr Universiteti</h1>
                <p class="text-gray-500 text-sm leading-relaxed mb-6">
                    Yangi Asr Universiteti — O'zbekistonning yetakchi nodavlat oliy ta'lim muassasasi bo'lib, zamonaviy ta'lim standartlari, innovatsion yondashuvlar va xalqaro hamkorlik asosida faoliyat yuritadi. Universitetning asosiy maqsadi — yosh avlodni bilimli, ma'naviyatli, raqobatbardosh kadrlar sifatida tayyorlash, jamiyat va davlat taraqqiyotiga hissa qo'shishdir.
                </p>

                <!-- Statistika qatori — namunadagi kabi bir xil (indigo)
                     rangli, 4 ustunli. -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <div
                        v-for="stat in stats"
                        :key="stat.label"
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 text-center transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
                    >
                        <div class="w-10 h-10 mx-auto rounded-xl flex items-center justify-center mb-2.5 bg-brand-50">
                            <Icon :icon="stat.icon" class="w-5 h-5 text-brand-600" />
                        </div>
                        <p class="text-xl font-bold text-navy-900">{{ stat.value }}</p>
                        <p class="text-gray-500 text-[11px] mt-0.5 leading-tight">{{ stat.label }}</p>
                    </div>
                </div>
            </div>

            <!-- Video karta — Bosh sahifadagi (Mission.vue) video kartochkasi
                 bilan bir xil uslubda: fon surat + pastdan to'q ko'k gradient
                 + play tugma, endi "qisilib" qolmasligi uchun kengroq va
                 balandroq. MUHIM: haqiqiy "Universitet hayoti" video/surati
                 tayyor bo'lgach, quyidagi vaqtinchalik /sliders/slide2.jpg
                 manzili shu bilan almashtiriladi. -->
            <button
                type="button"
                @click="videoOpen = true"
                class="relative block w-full lg:w-[360px] flex-shrink-0 rounded-2xl overflow-hidden group"
                style="height: 320px"
            >
                <img
                    src="/sliders/slide2.png"
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
                        <span class="block text-white/70 text-xs leading-tight mt-0.5">Video tanishtiruv</span>
                    </span>
                </span>

                <span class="absolute right-4 bottom-4 text-white/80 text-xs font-medium">03:24</span>
            </button>
        </div>

        <!-- Asosiy yo'nalishlar + CTA — namunadagi kabi BITTA qatorda: 4 ta
             yo'nalish kartasi + CTA karta (kengroq, 2 ustun egallaydi).
             MUHIM: 4 ta yo'nalish kartasi hozircha "#" manziliga ishora
             qiladi, chunki ularning haqiqiy sahifalari hali qurilmagan. -->
        <div>
            <h2 class="flex items-center gap-2.5 text-lg font-bold text-navy-900 mb-5">
                <span class="w-8 h-8 rounded-lg bg-brand-50 flex items-center justify-center">
                    <Icon icon="mdi:compass-outline" class="w-[18px] h-[18px] text-brand-600" />
                </span>
                Asosiy yo'nalishlar
            </h2>
            <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
                <div
                    v-for="dir in directions"
                    :key="dir.title"
                    class="lg:col-span-1 bg-white rounded-2xl border border-gray-100 shadow-sm p-5 transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
                >
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center mb-4 bg-brand-50">
                        <Icon :icon="dir.icon" class="w-5 h-5 text-brand-600" />
                    </div>
                    <h3 class="font-semibold text-navy-900 mb-2 text-sm">{{ dir.title }}</h3>
                    <p class="text-gray-500 text-xs leading-relaxed mb-4">{{ dir.desc }}</p>
                    <Link
                        :href="dir.href"
                        class="inline-flex items-center gap-1.5 text-brand-600 text-xs font-semibold hover:gap-2.5 transition-all duration-300"
                    >
                        Batafsil
                        <Icon icon="mdi:arrow-right" class="w-3.5 h-3.5" />
                    </Link>
                </div>

                <!-- CTA karta — 4 tadan keyin, 2 ustunni egallaydi (namunadagi
                     kabi qatorning bir qismi, alohida to'liq kenglikdagi
                     blok emas). "Qabul haqida" tugmasi hozircha mavjud
                     bo'lgan yagona qabul manziliga (/qabul/ariza)
                     yo'naltirilgan. -->
                <div class="col-span-2 relative overflow-hidden rounded-2xl p-5 flex flex-col justify-between" style="background: linear-gradient(135deg, var(--color-navy-700), var(--color-brand-700))">
                    <!-- Burchakdagi yagona icon o'rniga naqsh: aylana halqa +
                         yumshoq shar + qiya kvadrat, PageHero'dagi dekorativ
                         uslubga mos. -->
                    <div class="absolute inset-0 pointer-events-none overflow-hidden" aria-hidden="true">
                        <span class="absolute -right-5 -top-7 w-24 h-24 rounded-full border-2 border-white/20"></span>
                        <span class="absolute right-3 bottom-3 w-14 h-14 rounded-full bg-white/10"></span>
                        <span class="absolute right-14 top-3 w-7 h-7 rounded-lg bg-white/15 rotate-45"></span>
                    </div>
                    <div class="relative">
                        <h3 class="text-white text-base font-bold leading-snug mb-2">Kechagi bilim — bugungi muvaffaqiyat!</h3>
                        <p class="text-white/80 text-xs leading-relaxed mb-4">
                            Yangi Asr Universiteti sizni orzuingizdagi kelajak sari chorlaydi.
                        </p>
                    </div>
                    <Link
                        href="/qabul/ariza"
                        class="relative inline-flex items-center gap-2 px-4 py-2.5 bg-white text-navy-900 rounded-xl text-xs font-bold hover:bg-gray-50 transition w-max"
                    >
                        Qabul haqida
                        <Icon icon="mdi:arrow-right" class="w-3.5 h-3.5" />
                    </Link>
                </div>
            </div>
        </div>

        <!-- Video modal -->
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
    { label: 'Kafedralar',              icon: 'mdi:bank-outline',          value: '20+' },
    { label: "Yo'nalishlar",            icon: 'mdi:map-marker-outline',    value: '30+' },
    { label: 'Talabalar',               icon: 'mdi:account-group-outline', value: '3000+' },
    { label: "Professor-o'qituvchilar", icon: 'mdi:account-outline',       value: '200+' },
]

const directions = [
    {
        title: "Ta'lim",
        desc: "Zamonaviy bilim va innovatsion metodlar asosida ta'lim.",
        icon: 'mdi:certificate-outline',
        href: '#',
    },
    {
        title: 'Ilm-fan',
        desc: 'Tadqiqot va innovatsiyalar bilan kelajak sari.',
        icon: 'mdi:flask-outline',
        href: '#',
    },
    {
        title: 'Xalqaro hamkorlik',
        desc: 'Dunyo universitetlari bilan aloqada.',
        icon: 'mdi:earth',
        href: '#',
    },
    {
        title: 'Rivojlanish',
        desc: 'Talabalar uchun keng imkoniyatlar.',
        icon: 'mdi:send-outline',
        href: '#',
    },
]
</script>
