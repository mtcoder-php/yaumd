<template>
    <aside class="lg:w-64 flex-shrink-0">
        <nav class="bg-white rounded-2xl border border-gray-100 shadow-sm divide-y divide-gray-50 overflow-hidden">
            <div v-for="section in sections" :key="section.title">
                <button
                    type="button"
                    @click="toggle(section.title)"
                    class="w-full flex items-center justify-between gap-2 px-4 py-3.5 text-sm font-bold text-navy-900 hover:bg-gray-50 transition"
                >
                    <span class="flex items-center gap-2.5">
                        <Icon :icon="section.icon" class="w-[18px] h-[18px] text-brand-600" />
                        {{ section.title }}
                    </span>
                    <Icon
                        icon="mdi:chevron-down"
                        class="w-4 h-4 text-gray-400 transition-transform flex-shrink-0"
                        :class="{ 'rotate-180': isOpen(section.title) }"
                    />
                </button>

                <ul v-show="isOpen(section.title)" class="pb-2">
                    <li v-for="child in section.children" :key="child.href">
                        <Link
                            :href="child.href"
                            class="flex items-center justify-between gap-2 pl-10 pr-4 py-2 text-sm transition"
                            :class="isActive(child.href)
                                ? 'text-brand-700 bg-brand-50 font-semibold'
                                : 'text-gray-500 hover:text-navy-900 hover:bg-gray-50'"
                        >
                            <span class="flex items-center gap-2">
                                <span class="w-1 h-1 rounded-full bg-current flex-shrink-0"></span>
                                {{ child.title }}
                            </span>
                            <Icon v-if="isActive(child.href)" icon="mdi:arrow-right" class="w-3.5 h-3.5 flex-shrink-0" />
                        </Link>
                    </li>
                </ul>
            </div>
        </nav>
    </aside>
</template>

<script setup>
import { ref } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

const props = defineProps({
    // Faol (joriy) sahifa manzili — shu manzilni o'z ichiga olgan bo'lim
    // avtomatik ochiq holatda boshlanadi va havola ajratib ko'rsatiladi.
    activeHref: { type: String, default: '' },
})

const page = usePage()
const currentHref = props.activeHref || page.url

// MUHIM: bu ro'yxat "Universitet haqida" bo'limi sahifalari uchun umumiy
// (barcha ichki sahifalarda bir xil) chap tomon navigatsiyasi — hozircha
// faqat "Umumiy ma'lumot" (/university/about/general) haqiqiy sahifaga ega,
// qolganlari navbatma-navbat qurilgach shu havolalar ishga tushadi.
const sections = [
    {
        icon: 'mdi:folder-outline',
        title: 'Universitet haqida',
        children: [
            { title: "Umumiy ma'lumot", href: '/university/about/general' },
            { title: 'Universitet tuzilmasi', href: '/university/about/structure' },
            { title: "Professor & o'qituvchilar", href: '/university/about/staff' },
            { title: 'Universitet nizomi', href: '/university/about/charter' },
            { title: 'Sertifikatlar', href: '/university/about/certificates' },
            { title: "O'quv binolari", href: '/university/about/buildings' },
            { title: 'Talabalar turar joylari', href: '/university/about/dormitories' },
        ],
    },
    {
        icon: 'mdi:folder-outline',
        title: 'Yangiliklar',
        children: [
            { title: 'Yangiliklar', href: '/yangiliklar' },
            { title: "E'lonlar", href: '/elonlar' },
            { title: 'Fotogalereya', href: '/galereya/foto' },
            { title: 'Videogalereya', href: '/galereya/video' },
        ],
    },
    {
        icon: 'mdi:folder-outline',
        title: 'Ilmiy kengash',
        children: [
            { title: 'Ilmiy kengash tarkibi', href: '/kengash/tarkib' },
            { title: 'Ilmiy kengash qarorlari', href: '/kengash/qarorlar' },
        ],
    },
    {
        icon: 'mdi:certificate-outline',
        title: 'Ilmiy maqolalar',
        children: [
            { title: 'Xorijiy jurnallar', href: '/maqolalar/xorijiy' },
            { title: 'Mahalliy jurnallar', href: '/maqolalar/mahalliy' },
        ],
    },
    {
        icon: 'mdi:file-document-outline',
        title: 'Normativ hujjatlar',
        children: [
            { title: "Ta'lim to'g'risida", href: '/hujjatlar/talim' },
            { title: 'Prezident farmonlari', href: '/hujjatlar/farmonlar' },
            { title: 'Hukumat qarorlari', href: '/hujjatlar/qarorlar' },
            { title: 'Davlat dasturlari', href: '/hujjatlar/dasturlar' },
            { title: "Davlat ta'lim standarti", href: '/hujjatlar/standart' },
        ],
    },
]

const isActive = (href) => href === currentHref

// Joriy sahifani o'z ichiga olgan bo'lim boshida avtomatik ochiq bo'lishi uchun
const findSectionWithActive = () => sections.find(s => s.children.some(c => isActive(c.href)))

const openSection = ref(findSectionWithActive()?.title || sections[0].title)

const isOpen = (title) => openSection.value === title
const toggle = (title) => {
    openSection.value = openSection.value === title ? '' : title
}
</script>
