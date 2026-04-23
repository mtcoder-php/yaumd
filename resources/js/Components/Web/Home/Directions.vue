<template>
    <section class="py-14 bg-gray-50">
        <div class="container mx-auto px-4">

            <!-- Sarlavha -->
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Ta'lim yo'nalishlari</h2>
                <p class="text-gray-500 text-sm">Zamonaviy ta'lim dasturlari va yo'nalishlarimiz bilan tanishing</p>
            </div>

            <!-- Filter tabs — faqat desktop -->
            <div class="hidden md:flex justify-center mb-8">
                <div class="flex items-center gap-1 bg-gray-100 rounded-2xl p-1.5 w-max">
                    <button
                        @click="selectTab(null)"
                        class="flex-shrink-0 px-5 py-2 rounded-xl text-sm font-medium transition-all duration-200 whitespace-nowrap"
                        :class="activeTab === null ? 'bg-white shadow-sm font-semibold tab-active' : 'text-gray-500 hover:text-gray-700'"
                    >
                        Barchasi
                    </button>
                    <button
                        v-for="faculty in demoFaculties"
                        :key="faculty.id"
                        @click="selectTab(faculty.id)"
                        class="flex-shrink-0 px-5 py-2 rounded-xl text-sm font-medium transition-all duration-200 whitespace-nowrap"
                        :class="activeTab === faculty.id ? 'bg-white shadow-sm font-semibold tab-active' : 'text-gray-500 hover:text-gray-700'"
                    >
                        {{ faculty.name }}
                    </button>
                </div>
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

                <div class="overflow-hidden py-4" ref="sliderRef">
                    <div
                        class="flex gap-5 transition-transform duration-500 ease-in-out"
                        :style="{ transform: `translateX(-${sliderOffset}px)` }"
                    >
                        <div
                            v-for="direction in filteredDirections"
                            :key="direction.id"
                            class="direction-card flex-shrink-0 bg-white rounded-2xl overflow-hidden border border-gray-100 group cursor-pointer"
                        >
                            <!-- Rasm yoki gradient -->
                            <div class="relative overflow-hidden" style="height: 200px">
                                <img
                                    v-if="direction.image"
                                    :src="direction.image"
                                    :alt="direction.name"
                                    class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                                >
                                <div
                                    v-else
                                    class="w-full h-full flex items-center justify-center transition-transform duration-500 group-hover:scale-105"
                                    :style="{ background: direction.gradient }"
                                >
                                    <Icon :icon="direction.icon" class="w-16 h-16 text-white/70" />
                                </div>

                                <!-- Wishlist -->
                                <button class="absolute top-3 right-3 w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-md hover:bg-red-50 transition group/btn">
                                    <Icon icon="mdi:heart-outline" class="w-4 h-4 text-gray-400 group-hover/btn:text-red-500 transition" />
                                </button>

                                <!-- Daraja -->
                                <span
                                    class="absolute top-3 left-3 px-2.5 py-1 text-xs font-semibold rounded-lg text-white"
                                    :class="direction.degree === 'bachelor' ? 'bg-blue-600' : 'bg-purple-600'"
                                >
                                    {{ direction.degree === 'bachelor' ? 'Bakalavr' : 'Magistr' }}
                                </span>
                            </div>

                            <!-- Kontent -->
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 text-sm leading-snug mb-3 line-clamp-2 group-hover:text-[#0f3460] transition-colors duration-300" style="min-height: 40px">
                                    {{ direction.name }}
                                </h3>
                                <div class="flex items-center gap-4 text-xs text-gray-500 mb-3">
                                    <span class="flex items-center gap-1">
                                        <Icon icon="mdi:clock-outline" class="w-3.5 h-3.5" />
                                        {{ direction.duration }} yil
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <Icon icon="mdi:account-group-outline" class="w-3.5 h-3.5" />
                                        {{ direction.quota }} o'rin
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="flex items-center gap-1 text-xs text-gray-500">
                                        <Icon icon="mdi:cash" class="w-3.5 h-3.5" />
                                        {{ direction.price }}
                                    </span>
                                    <span class="flex items-center gap-1 text-xs text-yellow-500 font-medium">
                                        <Icon icon="mdi:star" class="w-3.5 h-3.5" />
                                        {{ direction.rating }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full flex items-center justify-center" :style="{ background: direction.gradient }">
                                            <Icon icon="mdi:school" class="w-3.5 h-3.5 text-white" />
                                        </div>
                                        <span class="text-xs text-gray-500 truncate max-w-[100px]">{{ direction.faculty }}</span>
                                    </div>
                                    <span class="flex items-center gap-1 text-xs font-semibold group-hover:gap-2 transition-all duration-300" style="color: #0f3460">
                                        Batafsil
                                        <Icon icon="mdi:arrow-right" class="w-3.5 h-3.5" />
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Barchasi (mobile) -->
            <div class="text-center mt-6 sm:hidden">
                <a href="/qabul/yonalishlar" class="inline-flex items-center gap-2 px-6 py-2.5 border-2 text-sm font-semibold rounded-xl transition-all hover:text-white" style="border-color: #0f3460; color: #0f3460; background: transparent" onmouseover="this.style.background='#0f3460'" onmouseout="this.style.background='transparent'">
                    Barchasi <Icon icon="mdi:arrow-right" class="w-4 h-4" />
                </a>
            </div>

        </div>
    </section>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { Icon } from '@iconify/vue'

const activeTab    = ref(null)
const sliderOffset = ref(0)
const sliderRef    = ref(null)
const windowWidth  = ref(window.innerWidth)

const onResize = () => { windowWidth.value = window.innerWidth; sliderOffset.value = 0 }
onMounted(() => {
    window.addEventListener('resize', onResize)
    // sliderRef tayyor bo'lganda qayta hisoblash uchun
    setTimeout(() => { windowWidth.value = window.innerWidth }, 100)
})
onUnmounted(() => window.removeEventListener('resize', onResize))

const cardWidth = computed(() => {
    if (windowWidth.value < 640) return windowWidth.value * 0.9 - 32
    if (windowWidth.value < 1024) return 280
    const containerWidth = sliderRef.value?.offsetWidth || (windowWidth.value * 0.9 - 32)
    return Math.floor((containerWidth - 3 * 20) / 4)
})

const gap = 20
const stepWidth = computed(() => cardWidth.value + gap)

const maxOffset = computed(() => {
    const total = filteredDirections.value.length
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
const selectTab = (id) => { activeTab.value = id; sliderOffset.value = 0 }
watch(activeTab, () => { sliderOffset.value = 0 })

const demoFaculties = [
    { id: 1, name: 'Sharq filologiyasi' },
    { id: 2, name: 'Tillar' },
    { id: 3, name: 'Maxsus pedagogika' },
    { id: 4, name: "Umumta'lim" },
]

const demoDirections = [
    { id: 1,  faculty_id: 1, faculty: 'Sharq filologiyasi', name: 'Arab tili filologiyasi',           degree: 'bachelor', duration: 4, quota: 25, price: "18 mln so'm", rating: '4.9', image: '/directions/image-1.jpg', gradient: 'linear-gradient(135deg, #0f3460, #533483)', icon: 'mdi:book-alphabet'    },
    { id: 2,  faculty_id: 1, faculty: 'Sharq filologiyasi', name: 'Sharq mumtoz tili',                degree: 'bachelor', duration: 4, quota: 20, price: "18 mln so'm", rating: '4.8', image: '/directions/image-1.jpg', gradient: 'linear-gradient(135deg, #533483, #0f3460)', icon: 'mdi:translate'        },
    { id: 3,  faculty_id: 2, faculty: 'Tillar',             name: 'Ingliz tili',                      degree: 'bachelor', duration: 4, quota: 50, price: "20 mln so'm", rating: '4.9', image: '/directions/image-1.jpg', gradient: 'linear-gradient(135deg, #0f3460, #1e6091)', icon: 'mdi:earth'            },
    { id: 4,  faculty_id: 2, faculty: 'Tillar',             name: 'Xitoy tili',                       degree: 'bachelor', duration: 4, quota: 30, price: "22 mln so'm", rating: '4.7', image: '/directions/image-1.jpg', gradient: 'linear-gradient(135deg, #533483, #7c3aed)', icon: 'mdi:ideogram-cjk'    },
    { id: 5,  faculty_id: 2, faculty: 'Tillar',             name: 'Koreys tili',                      degree: 'bachelor', duration: 4, quota: 25, price: "22 mln so'm", rating: '4.8', image: '/directions/image-1.jpg', gradient: 'linear-gradient(135deg, #1e3a8a, #533483)', icon: 'mdi:alphabetical'    },
    { id: 6,  faculty_id: 3, faculty: 'Maxsus pedagogika',  name: 'Logopediya',                       degree: 'bachelor', duration: 4, quota: 20, price: "16 mln so'm", rating: '4.6', image: '/directions/image-1.jpg', gradient: 'linear-gradient(135deg, #0f3460, #533483)', icon: 'mdi:account-voice'   },
    { id: 7,  faculty_id: 3, faculty: 'Maxsus pedagogika',  name: 'Psixologiya',                      degree: 'bachelor', duration: 4, quota: 30, price: "16 mln so'm", rating: '4.7', image: '/directions/image-1.jpg', gradient: 'linear-gradient(135deg, #533483, #1e3a8a)', icon: 'mdi:head-cog-outline'},
    { id: 8,  faculty_id: 4, faculty: "Umumta'lim",         name: 'Dasturiy injiniring',              degree: 'bachelor', duration: 4, quota: 40, price: "24 mln so'm", rating: '5.0', image: '/directions/image-1.jpg', gradient: 'linear-gradient(135deg, #1e3a8a, #0f3460)', icon: 'mdi:code-braces'     },
    { id: 9,  faculty_id: 4, faculty: "Umumta'lim",         name: 'Iqtisodiyot',                      degree: 'bachelor', duration: 4, quota: 35, price: "18 mln so'm", rating: '4.8', image: '/directions/image-1.jpg', gradient: 'linear-gradient(135deg, #0f3460, #7c3aed)', icon: 'mdi:chart-line'      },
    { id: 10, faculty_id: 2, faculty: 'Tillar',             name: 'Lingvistika: Ingliz tili (Magistr)',degree: 'master',   duration: 2, quota: 15, price: "22 mln so'm", rating: '4.9', image: '/directions/image-1.jpg', gradient: 'linear-gradient(135deg, #533483, #0f3460)', icon: 'mdi:school-outline'  },
]

const filteredDirections = computed(() => {
    if (!activeTab.value) return demoDirections
    return demoDirections.filter(d => d.faculty_id === activeTab.value)
})
</script>

<style scoped>
.tab-active { color: #0f3460; }

.direction-card {
    width: v-bind('cardWidth + "px"');
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.3s ease, border-color 0.3s ease;
    position: relative;
    z-index: 1;
}
.direction-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 24px 48px rgba(15,52,96,0.18);
    border-color: #c4b5fd;
    z-index: 10;
}

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
