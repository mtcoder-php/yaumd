<template>
    <div>
        <div class="flex items-center justify-between mb-6">
            <h2 class="flex items-center gap-2 text-2xl font-bold text-navy-900">
                <Icon icon="mdi:newspaper-variant-outline" class="w-6 h-6 text-brand-600" />
                So'nggi yangiliklar
            </h2>
            <Link href="/yangiliklar" class="flex items-center gap-1 text-sm font-semibold text-brand-600 hover:text-brand-700 transition">
                Barchasi
                <Icon icon="mdi:arrow-right" class="w-4 h-4" />
            </Link>
        </div>

        <!-- Yangiliklar grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8 items-stretch">
            <a
                v-for="article in displayNews"
                :key="article.id"
                :href="`/yangiliklar/${article.slug || '#'}`"
                class="news-card h-full bg-white rounded-2xl overflow-hidden border border-gray-100 flex flex-col"
            >
                <div class="relative overflow-hidden flex-shrink-0" style="height: 180px">
                    <img
                        v-if="article.image"
                        :src="article.image"
                        :alt="article.title_uz"
                        class="w-full h-full object-cover news-img transition-transform duration-500"
                    >
                    <div
                        v-else
                        class="news-img w-full h-full flex items-center justify-center transition-transform duration-500"
                        :style="{ background: getGradient(article.id) }"
                    >
                        <Icon icon="mdi:newspaper-variant-outline" class="w-10 h-10 text-white/60" />
                    </div>
                </div>
                <div class="p-3 flex flex-col flex-1">
                    <div class="flex items-center justify-between gap-2 mb-1.5">
                        <span
                            class="px-2 py-0.5 text-[10px] font-semibold rounded-full text-white"
                            :style="{ background: article.category?.color || '#3b82f6' }"
                        >
                            {{ article.category?.name_uz || 'Yangilik' }}
                        </span>
                        <span class="text-[11px] text-gray-400 flex-shrink-0">{{ formatDate(article.published_at) }}</span>
                    </div>
                    <h3 class="news-title font-semibold text-navy-900 text-[13px] leading-snug line-clamp-2 mb-2 min-h-[36px] transition-colors duration-300">
                        {{ article.title_uz }}
                    </h3>
                    <div class="mt-auto flex items-center justify-between gap-2">
                        <span class="news-link flex items-center gap-1 text-xs font-semibold text-brand-600">
                            Batafsil
                            <Icon icon="mdi:arrow-right" class="w-3 h-3 news-arrow transition-transform duration-300" />
                        </span>
                        <span class="flex items-center gap-1 text-[11px] text-gray-400">
                            <Icon icon="mdi:eye-outline" class="w-3.5 h-3.5" />
                            {{ formatViews(article.views) }}
                        </span>
                    </div>
                </div>
            </a>
        </div>

        <!-- Mini statistika -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-navy-50 rounded-2xl p-5">
            <div v-for="stat in miniStats" :key="stat.label" class="flex items-center gap-3">
                <span class="flex-shrink-0 w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm">
                    <Icon :icon="stat.icon" class="w-5 h-5 text-brand-600" />
                </span>
                <div>
                    <p class="text-brand-900 font-bold text-lg leading-tight">{{ stat.value }}</p>
                    <p class="text-navy-400 text-xs leading-tight">{{ stat.label }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

const props = defineProps({
    news: { type: Array, default: () => [] },
    lang: { type: String, default: 'uz' },
})

// MUHIM: bular faqat DB'da hali yangilik bo'lmaganda ko'rinadigan
// NAMUNA (demo) yozuvlar — rasm o'rniga vaqtinchalik /public/blogs/blog-*.jpg
// qo'yilgan. Haqiqiy yangilik rasmi bo'lsa (admin panelda yuklangan),
// backend'dan kelgan `news` propi ustunlik qiladi.
const demoNews = [
    { id: 1, slug: 'yangilik-1', title_uz: 'Yangi Asr universiteti 2025-yil qabul jarayonini boshladi', image: '/blogs/blog-1.jpg', published_at: '2025-03-15', views: 1240, category: { name_uz: 'Qabul', color: '#4f46e5' } },
    { id: 2, slug: 'yangilik-2', title_uz: 'Xalqaro hamkorlik doirasida yangi shartnoma imzolandi', image: '/blogs/blog-2.jpg', published_at: '2025-03-10', views: 856, category: { name_uz: 'Xalqaro', color: '#0891b2' } },
    { id: 3, slug: 'yangilik-3', title_uz: 'Talabalarimiz respublika olimpiadasida g\'olib bo\'ldi', image: '/blogs/blog-1.jpg', published_at: '2025-03-08', views: 2103, category: { name_uz: 'Tadbir', color: '#22c55e' } },
    { id: 4, slug: 'yangilik-4', title_uz: 'Yangi o\'quv laboratoriyasi ochildi', image: '/blogs/blog-2.jpg', published_at: '2025-03-05', views: 431, category: { name_uz: "Ta'lim", color: '#f97316' } },
]

const displayNews = computed(() => (props.news?.length ? props.news.slice(0, 4) : demoNews))

// MUHIM: sana har doim DD.MM.YYYY ko'rinishida chiqishi uchun brauzer/server
// lokalizatsiyasiga bog'liq bo'lmagan qo'lda formatlash ishlatilgan.
const formatDate = (date) => {
    if (!date) return ''
    const d = new Date(date)
    const day = String(d.getDate()).padStart(2, '0')
    const month = String(d.getMonth() + 1).padStart(2, '0')
    return `${day}.${month}.${d.getFullYear()}`
}

const formatViews = (views) => {
    const n = Number(views) || 0
    return String(n).replace(/\B(?=(\d{3})+(?!\d))/g, ' ')
}

const gradients = [
    'linear-gradient(135deg, #16255a, #4f46e5)',
    'linear-gradient(135deg, #4f46e5, #7c3aed)',
    'linear-gradient(135deg, #101c46, #16255a)',
    'linear-gradient(135deg, #16255a, #1c2f68)',
]
const getGradient = (id) => gradients[id % gradients.length]

const miniStats = [
    { label: 'Talabalar',            value: '3 000+', icon: 'mdi:account-group-outline' },
    { label: "Professor-o'qituvchi", value: '200+',   icon: 'mdi:school-outline' },
    { label: "Ta'lim yo'nalishlari", value: '100+',   icon: 'mdi:book-open-outline' },
    { label: 'Kampus hududi',        value: '50+',    icon: 'mdi:map-marker-radius-outline' },
]
</script>

<style scoped>
.news-card {
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.3s ease, border-color 0.3s ease;
}
.news-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 32px rgba(16,28,70,0.14);
    border-color: var(--color-brand-200);
}
.news-card:hover .news-img { transform: scale(1.06); }
.news-card:hover .news-title { color: var(--color-brand-700); }
.news-card:hover .news-arrow { transform: translateX(3px); }
</style>
