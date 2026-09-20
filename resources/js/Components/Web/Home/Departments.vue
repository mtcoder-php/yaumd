<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h2 class="flex items-center gap-2 text-2xl font-bold text-navy-900">
                <Icon icon="mdi:bank-outline" class="w-6 h-6 text-brand-600" />
                Kafedralar
            </h2>
            <Link href="/tuzilma/kafedralar" class="flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700 transition">
                Barchasi
                <Icon icon="mdi:arrow-right" class="w-4 h-4" />
            </Link>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 items-stretch">
            <Link
                v-for="(dept, i) in displayDepartments"
                :key="i"
                :href="dept.href"
                class="dept-card h-full group rounded-2xl overflow-hidden border border-gray-100 bg-white flex flex-col"
            >
                <!-- MUHIM: vaqtinchalik surat — /public/blogs/blog-*.jpg. Har bir
                     kafedraga tegishli haqiqiy surat tayyor bo'lgach shu joyga
                     almashtiriladi. -->
                <div class="relative overflow-hidden flex-shrink-0" style="height: 150px">
                    <img
                        :src="dept.image"
                        :alt="dept.name"
                        class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                    >
                    <span class="absolute top-3 left-3 flex items-center justify-center w-9 h-9 rounded-lg bg-white/90 backdrop-blur-sm shadow-sm">
                        <Icon icon="mdi:bank-outline" class="w-4 h-4 text-brand-600" />
                    </span>
                </div>
                <div class="p-4 flex flex-col flex-1">
                    <h3 class="font-semibold text-navy-900 text-sm leading-snug line-clamp-2 min-h-[38px] mb-1 group-hover:text-brand-600 transition-colors">
                        {{ dept.name }}
                    </h3>
                    <p class="text-gray-400 text-xs leading-snug line-clamp-1 mb-3">{{ dept.subtitle }}</p>
                    <span class="mt-auto flex items-center gap-1 text-xs font-semibold text-brand-600">
                        Batafsil
                        <Icon icon="mdi:arrow-right" class="w-3.5 h-3.5" />
                    </span>
                </div>
            </Link>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

const props = defineProps({
    departments: { type: Array, default: () => [] },
    lang:        { type: String, default: 'uz' },
})

// MUHIM: bizda hozircha bittagina Fakultet bor, shuning uchun "Fakultetlar"
// emas, to'g'ridan-to'g'ri "Kafedralar" ko'rsatiladi. Haqiqiy kafedralar
// soni 4 tadan kam bo'lsa (yoki hali DB'ga kiritilmagan bo'lsa), quyidagi
// NAMUNA (demo) ro'yxat bilan to'ldiriladi — bular resources/js/Data/menuItems.js
// dagi "Tuzilma > Kafedralar" ro'yxati bilan bir xil.
const demoDepartments = [
    { name: 'Mumtoz sharq filologiyasi',    subtitle: "Til va adabiyot yo'nalishi", href: '/kafedralar/sharq',    isDemo: true },
    { name: 'Tillar',                        subtitle: "Chet tillari yo'nalishi",    href: '/kafedralar/tillar',   isDemo: true },
    { name: 'Maxsus pedagogika',             subtitle: "Pedagogika yo'nalishi",      href: '/kafedralar/pedagogika', isDemo: true },
    { name: "Maktab va maktabgacha ta'lim",  subtitle: "Ta'lim yo'nalishi",          href: '/kafedralar/maktab',   isDemo: true },
]

// MUHIM: har bir kafedraga mos haqiqiy surat qo'shilguncha, vaqtinchalik
// /public/blogs/blog-1.jpg va blog-2.jpg navbat bilan ishlatiladi.
const images = ['/blogs/blog-1.jpg', '/blogs/blog-2.jpg']

const displayDepartments = computed(() => {
    const real = (props.departments || []).slice(0, 4)
    const source = real.length >= 4 ? real : [...real, ...demoDepartments.slice(0, 4 - real.length)]
    return source.map((d, i) => ({
        name: d.isDemo ? d.name : (d[`name_${props.lang}`] || d.name_uz || d.name),
        subtitle: d.isDemo
            ? d.subtitle
            : (d.directions_count != null ? `${d.directions_count} ta ta'lim yo'nalishi` : (d.short_name || 'Kafedra')),
        image: d.image || images[i % images.length],
        href: d.href || '/tuzilma/kafedralar',
    }))
})
</script>

<style scoped>
.dept-card {
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.3s ease, border-color 0.3s ease;
}
.dept-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 32px rgba(16,28,70,0.14);
    border-color: var(--color-brand-200);
}
</style>
