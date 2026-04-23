<template>
    <section class="py-14 bg-gray-50">
        <div class="container mx-auto px-4">

            <!-- Sarlavha — o'rtadan -->
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Yangiliklar</h2>
                <p class="text-gray-500 text-sm">Universitetimiz hayotidan so'nggi voqealar</p>
            </div>

            <!-- Slider -->
            <div class="relative">

                <!-- Prev -->
                <button
                    v-if="canPrev"
                    @click="slidePrev"
                    class="slide-btn absolute -left-5 top-1/2 -translate-y-1/2 z-20 w-11 h-11 bg-white rounded-full border border-gray-200 flex items-center justify-center"
                >
                    <Icon icon="mdi:chevron-left" class="w-5 h-5 text-gray-600" />
                </button>

                <!-- Next -->
                <button
                    v-if="canNext"
                    @click="slideNext"
                    class="slide-btn absolute -right-5 top-1/2 -translate-y-1/2 z-20 w-11 h-11 bg-white rounded-full border border-gray-200 flex items-center justify-center"
                >
                    <Icon icon="mdi:chevron-right" class="w-5 h-5 text-gray-600" />
                </button>

                <!-- Cards -->
                <div class="overflow-hidden py-4" ref="sliderRef">
                    <div
                        class="flex gap-5 transition-transform duration-500 ease-in-out"
                        :style="{ transform: `translateX(-${sliderOffset}px)` }"
                    >
                        <a
                            v-for="article in displayNews"
                            :key="article.id"
                            :href="`/yangiliklar/${article.slug || '#'}`"
                            class="news-card flex-shrink-0 bg-white rounded-2xl overflow-hidden border border-gray-100 flex flex-col"
                        >
                            <!-- Rasm -->
                            <div class="relative overflow-hidden flex-shrink-0" style="height: 200px">
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
                                    <Icon icon="mdi:newspaper-variant-outline" class="w-14 h-14 text-white/60" />
                                </div>
                                <span
                                    class="absolute top-3 left-3 px-2.5 py-1 text-xs font-semibold rounded-full text-white"
                                    :style="{ background: article.category?.color || '#3b82f6' }"
                                >
                                    {{ article.category?.name_uz || 'Yangilik' }}
                                </span>
                            </div>

                            <!-- Kontent -->
                            <div class="p-4 flex flex-col flex-1">
                                <div class="flex items-center gap-3 text-xs text-gray-400 mb-2.5">
                                    <span class="flex items-center gap-1">
                                        <Icon icon="mdi:calendar-outline" class="w-3.5 h-3.5" />
                                        {{ formatDate(article.published_at) }}
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <Icon icon="mdi:eye-outline" class="w-3.5 h-3.5" />
                                        {{ article.views || 0 }}
                                    </span>
                                </div>
                                <h3 class="news-title font-semibold text-gray-900 text-sm leading-snug line-clamp-2 mb-2 transition-colors duration-300">
                                    {{ article.title_uz }}
                                </h3>
                                <p class="text-gray-500 text-xs line-clamp-2 flex-1 mb-3">
                                    {{ article.excerpt_uz }}
                                </p>
                                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-7 h-7 rounded-full flex items-center justify-center"
                                            :style="{ background: article.category?.color || '#3b82f6' }"
                                        >
                                            <Icon icon="mdi:account" class="w-3.5 h-3.5 text-white" />
                                        </div>
                                        <span class="text-xs text-gray-500">Tahririyat</span>
                                    </div>
                                    <span class="news-link flex items-center gap-1 text-xs font-semibold" style="color: #0f3460">
                                        Batafsil
                                        <Icon icon="mdi:arrow-right" class="w-3.5 h-3.5 news-arrow transition-transform duration-300" />
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Barchasi (mobile) -->
            <div class="text-center mt-6 sm:hidden">
                <a href="/yangiliklar" class="inline-flex items-center gap-2 px-6 py-2.5 border-2 text-sm font-semibold rounded-xl transition-all hover:text-white" style="border-color: #0f3460; color: #0f3460; background: transparent" onmouseover="this.style.background='#0f3460'" onmouseout="this.style.background='transparent'">
                    Barchasi <Icon icon="mdi:arrow-right" class="w-4 h-4" />
                </a>
            </div>

        </div>
    </section>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { Icon } from '@iconify/vue'

const props = defineProps({
    news: { type: Array, default: () => [] },
    lang: { type: String, default: 'uz' },
})

const sliderRef    = ref(null)
const sliderOffset = ref(0)
const windowWidth  = ref(window.innerWidth)

const onResize = () => { windowWidth.value = window.innerWidth; sliderOffset.value = 0 }
onMounted(() => {
    window.addEventListener('resize', onResize)
    setTimeout(() => { windowWidth.value = window.innerWidth }, 100)
})
onUnmounted(() => window.removeEventListener('resize', onResize))

// 4 ta card ko'rinsin
const cardWidth = computed(() => {
    if (windowWidth.value < 640) return windowWidth.value * 0.9 - 32
    if (windowWidth.value < 1024) return 280
    const containerWidth = sliderRef.value?.offsetWidth || (windowWidth.value * 0.9 - 32)
    return Math.floor((containerWidth - 3 * 20) / 4)
})

const gap = 20
const stepWidth = computed(() => cardWidth.value + gap)

const maxOffset = computed(() => {
    const total = displayNews.value.length
    const visible = windowWidth.value < 640 ? 1 : windowWidth.value < 1024 ? 2 : 4
    if (total <= visible) return 0
    return (total - visible) * stepWidth.value
})

const canPrev = computed(() => sliderOffset.value > 0)
const canNext = computed(() => sliderOffset.value < maxOffset.value)

const slideNext = () => {
    sliderOffset.value = Math.min(sliderOffset.value + stepWidth.value, maxOffset.value)
}
const slidePrev = () => {
    sliderOffset.value = Math.max(sliderOffset.value - stepWidth.value, 0)
}

const demoNews = [
    { id: 1, slug: 'yangilik-1', title_uz: 'Yangi Asr universiteti 2025-yil qabul jarayonini boshladi', excerpt_uz: 'Universitetimiz 2025-2026 o\'quv yili uchun qabul jarayonini rasman boshladi.', image: '/directions/image-1.jpg', views: 1250, published_at: '2025-03-15', category: { name_uz: 'Qabul', color: '#0f3460' } },
    { id: 2, slug: 'yangilik-2', title_uz: 'Xalqaro hamkorlik doirasida yangi shartnoma imzolandi', excerpt_uz: 'Universitetimiz xorijiy yetakchi universitetlar bilan hamkorlikni kengaytirmoqda.', image: 'https://yangiasr.uz/images/directions/2024-07-05-17-09-26_bdad3ae78ada740ecd684ae14c647edc.jpeg', views: 890, published_at: '2025-03-10', category: { name_uz: 'Xalqaro', color: '#533483' } },
    { id: 3, slug: 'yangilik-3', title_uz: 'Talabalarimiz respublika olimpiadasida g\'olib bo\'ldi', excerpt_uz: 'Universitetimiz talabalari respublika darajasidagi musobaqada birinchi o\'rinni egallashdi.', image: 'https://yangiasr.uz/images/directions/2025-01-02-13-49-12_0cadbdb0a46c03f5f6a76583ab79a0c1.jpeg', views: 2100, published_at: '2025-03-08', category: { name_uz: 'Tadbir', color: '#22c55e' } },
    { id: 4, slug: 'yangilik-4', title_uz: 'Yangi o\'quv laboratoriyasi ochildi', excerpt_uz: 'Zamonaviy jihozlar bilan ta\'minlangan yangi laboratoriya talabalarimizga xizmat ko\'rsata boshladi.', image: 'https://yangiasr.uz/images/directions/2025-01-02-13-51-01_5667e1ab99cac35f85e745b961a9e8ac.jpeg', views: 567, published_at: '2025-03-05', category: { name_uz: 'Ta\'lim', color: '#f97316' } },
    { id: 5, slug: 'yangilik-5', title_uz: 'Professor-o\'qituvchilar malaka oshirish kurslarini yakunladi', excerpt_uz: 'O\'qituvchilarimiz zamonaviy pedagogik texnologiyalar bo\'yicha malaka oshirdi.', image: 'https://yangiasr.uz/images/directions/2025-01-02-13-52-26_34fb63c8b2bc7b8eba5d0f737760fbc5.jpeg', views: 430, published_at: '2025-03-01', category: { name_uz: 'Ta\'lim', color: '#f97316' } },
    { id: 6, slug: 'yangilik-6', title_uz: 'Universitet sport musobaqalari yakunlandi', excerpt_uz: 'Yillik sport musobaqalarida talabalarimiz yuqori natijalarni ko\'rsatdi.', image: 'https://yangiasr.uz/images/directions/2025-01-02-13-44-35_50fcc7c5df99e0fd77bb2ff137240cc9.jpeg', views: 320, published_at: '2025-02-25', category: { name_uz: 'Sport', color: '#ef4444' } },
]

const displayNews = computed(() => props.news?.length ? props.news : demoNews)

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const gradients = [
    'linear-gradient(135deg, #0f3460, #533483)',
    'linear-gradient(135deg, #533483, #7c3aed)',
    'linear-gradient(135deg, #1e3a8a, #0f3460)',
    'linear-gradient(135deg, #0f3460, #1e6091)',
    'linear-gradient(135deg, #533483, #0f3460)',
    'linear-gradient(135deg, #1e3a8a, #533483)',
]

const getGradient = (id) => gradients[id % gradients.length]
</script>

<style scoped>
.news-card {
    width: v-bind('cardWidth + "px"');
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.3s ease, border-color 0.3s ease;
    position: relative;
    z-index: 1;
}
.news-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 24px 48px rgba(15,52,96,0.18);
    border-color: #c4b5fd;
    z-index: 10;
}
.news-card:hover .news-img { transform: scale(1.05); }
.news-card:hover .news-title { color: #0f3460; }
.news-card:hover .news-arrow { transform: translateX(4px); }

.slide-btn {
    box-shadow: 0 4px 16px rgba(0,0,0,0.12);
    transition: transform 0.2s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.2s ease, background 0.2s ease, border-color 0.2s ease;
}
.slide-btn:hover {
    transform: scale(1.15);
    box-shadow: 0 8px 24px rgba(15,52,96,0.35);
    background: #0f3460;
    border-color: #0f3460;
}
.slide-btn:hover .iconify { color: white !important; }
.slide-btn:active { transform: scale(0.95); }
</style>
