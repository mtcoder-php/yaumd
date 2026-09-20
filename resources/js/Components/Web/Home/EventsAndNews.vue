<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Tadbirlar -->
        <div class="rounded-2xl bg-white border-2 border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="flex items-center gap-2 font-bold text-navy-900">
                    <Icon icon="mdi:calendar-month-outline" class="w-5 h-5 text-brand-600" />
                    Tadbirlar
                </h3>
                <Link href="/elonlar" class="flex items-center gap-1 text-xs font-semibold text-brand-600 hover:text-brand-700 transition">
                    Barchasi
                    <Icon icon="mdi:arrow-right" class="w-3.5 h-3.5" />
                </Link>
            </div>
            <ul class="divide-y divide-gray-50">
                <li v-for="event in events" :key="event.title" class="flex items-start gap-3 py-3">
                    <div class="flex-shrink-0 w-11 h-11 rounded-lg bg-navy-50 flex flex-col items-center justify-center leading-none">
                        <span class="text-navy-900 font-bold text-sm">{{ event.day }}</span>
                        <span class="text-[10px] text-gray-400 uppercase">{{ event.month }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-navy-900 leading-snug line-clamp-2">{{ event.title }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ event.meta }}</p>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Yangiliklar -->
        <div class="rounded-2xl bg-white border-2 border-gray-100 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="flex items-center gap-2 font-bold text-navy-900">
                    <Icon icon="mdi:bell-outline" class="w-5 h-5 text-brand-600" />
                    Yangiliklar
                </h3>
                <Link href="/yangiliklar" class="flex items-center gap-1 text-xs font-semibold text-brand-600 hover:text-brand-700 transition">
                    Barchasi
                    <Icon icon="mdi:arrow-right" class="w-3.5 h-3.5" />
                </Link>
            </div>
            <ul class="divide-y divide-gray-50">
                <a
                    v-for="article in displayNews"
                    :key="article.id"
                    :href="`/yangiliklar/${article.slug || '#'}`"
                    class="flex items-start gap-3 py-3 group"
                >
                    <span class="flex-shrink-0 text-xs text-gray-400 w-20 pt-0.5">{{ formatDate(article.published_at) }}</span>
                    <p class="text-sm text-navy-900 leading-snug line-clamp-2 group-hover:text-brand-600 transition-colors">
                        {{ article.title_uz }}
                    </p>
                </a>
            </ul>
        </div>

    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

const props = defineProps({
    news: { type: Array, default: () => [] },
})

// MUHIM: hozircha alohida "Tadbirlar/E'lonlar" modeli mavjud emas
// (faqat NewsArticle bor), shuning uchun bu yerda vaqtinchalik namunaviy
// (demo) sanalar ishlatilgan — kelajakda alohida Event modeli qo'shilsa,
// shu yerga backend'dan real ma'lumot ulash kifoya.
const events = [
    { day: '15', month: 'may', title: "Oliy ta'lim muassasalari o'rtasida sport musobaqasi", meta: 'Soat 10:00 · Universitet sport majmuasi' },
    { day: '20', month: 'may', title: "\"Zamonaviy ta'lim — taraqqiyot kafolati\" mavzusida ilmiy-amaliy konferensiya", meta: 'Soat 10:00 · Katta majlislar zali' },
    { day: '25', month: 'may', title: 'Bitiruvchilar uchun uchrashuv', meta: 'Soat 14:00 · Konferensiya zali' },
]

const demoNews = [
    { id: 1, slug: 'yangilik-1', title_uz: "Yangi o'quv binosi foydalanishga topshirildi", published_at: '2025-06-12' },
    { id: 2, slug: 'yangilik-2', title_uz: "Xalqaro ilmiy-amaliy konferensiya o'tkazildi", published_at: '2025-06-05' },
    { id: 3, slug: 'yangilik-3', title_uz: 'Bitiruvchilar uchun maxsus uchrashuv', published_at: '2025-05-28' },
    { id: 4, slug: 'yangilik-4', title_uz: "Universitet va xorijiy oliy ta'lim muassasalari o'rtasida hamkorlik memorandumi imzolandi", published_at: '2025-05-20' },
]

const displayNews = computed(() => (props.news?.length ? props.news.slice(0, 4) : demoNews))

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric' })
}
</script>
