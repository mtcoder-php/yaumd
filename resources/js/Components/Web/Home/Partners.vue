<template>
    <section class="partners-section py-12">

        <!-- Sarlavha — container ichida -->
        <div class="container mx-auto px-4">
            <div class="text-center mb-10">
                <h2 class="text-2xl font-bold text-white mb-2">Hamkorlar</h2>
                <p class="text-white/60 text-sm">Bizning ishonchli hamkorlarimiz</p>
            </div>
        </div>

        <!-- Slider — to'liq kenglikda, container tashqarisida -->
        <div class="slider-outer">
            <!-- Fade — to'liq kenglikda -->
            <div class="fade-left"></div>
            <div class="fade-right"></div>

            <div class="slider-wrapper py-6">
                <div class="partners-track flex gap-5">
                    <template v-for="n in 3" :key="n">
                        <a
                            v-for="partner in displayPartners"
                            :key="`${n}-${partner.id}`"
                            :href="partner.url || '#'"
                            target="_blank"
                            class="partner-card flex-shrink-0 flex flex-col items-center justify-center gap-4 rounded-2xl bg-white"
                        >
                            <div class="flex items-center justify-center" style="height: 64px">
                                <img
                                    :src="partner.logo"
                                    :alt="partner.name"
                                    class="partner-logo max-h-16 max-w-full object-contain transition-transform duration-300"
                                >
                            </div>
                            <span class="text-xs font-medium text-gray-700 text-center leading-tight px-2">
                                {{ partner.name }}
                            </span>
                        </a>
                    </template>
                </div>
            </div>
        </div>

    </section>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    partners: { type: Array, default: () => [] },
})

const demoPartners = [
    { id: 1, name: "Oliy ta'lim vazirligi",                      logo: "/partners/edu-gov.png",        url: "#" },
    { id: 2, name: "Xalqaro islomshunoslik akademiyasi",          logo: "/partners/islamic-academy.png", url: "#" },
    { id: 3, name: "Grand ta'lim maktabi",                       logo: "/partners/grand-school.png",   url: "#" },
    { id: 4, name: "Grand ta'lim markazi",                       logo: "/partners/grand-edu.png",      url: "#" },
    { id: 5, name: "Nankin universiteti",                        logo: "/partners/nanjing.png",        url: "#" },
    { id: 6, name: "O'zbekiston milliy pedagogika universiteti", logo: "/partners/tdpu.avif",          url: "#" },
]

const displayPartners = computed(() =>
    props.partners?.length ? props.partners : demoPartners
)
</script>

<style scoped>
.partners-section {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 40%, #0f3460 70%, #533483 100%);
    position: relative;
}

.partners-section::before {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 280px; height: 280px;
    background: radial-gradient(circle, rgba(102,126,234,0.3) 0%, transparent 70%);
    border-radius: 50%;
}

.partners-section::after {
    content: '';
    position: absolute;
    bottom: -60px; left: -60px;
    width: 240px; height: 240px;
    background: radial-gradient(circle, rgba(240,147,251,0.25) 0%, transparent 70%);
    border-radius: 50%;
}

/* Slider tashqi wrapper */
.slider-outer {
    position: relative;
    width: 100%;
    overflow: hidden;
}

/* Fade — to'liq kenglik bo'ylab */
.fade-left,
.fade-right {
    position: absolute;
    top: 0;
    bottom: 0;
    width: 120px;
    z-index: 10;
    pointer-events: none;
}

.fade-left {
    left: 0;
    background: linear-gradient(to right, #1a1a2e 0%, transparent 100%);
}

.fade-right {
    right: 0;
    background: linear-gradient(to left, #533483 0%, transparent 100%);
}

/* Slider wrapper — vertikal overflow uchun */
.slider-wrapper {
    overflow: visible;
}

/* Track — auto scroll */
.partners-track {
    display: flex;
    width: max-content;
    animation: scroll 30s linear infinite;
}

.partners-track:hover {
    animation-play-state: paused;
}

@keyframes scroll {
    0%   { transform: translateX(0); }
    100% { transform: translateX(-33.333%); }
}

/* Card */
.partner-card {
    width: 180px;
    min-height: 150px;
    padding: 24px 16px;
    position: relative;
    z-index: 1;
    border: 1px solid rgba(255,255,255,0.1);
    box-shadow: 0 4px 20px rgba(0,0,0,0.2);
    transition: transform 0.3s cubic-bezier(0.34,1.56,0.64,1), box-shadow 0.3s ease;
}

.partner-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 48px rgba(0,0,0,0.35);
    z-index: 20;
}

.partner-card:hover .partner-logo {
    transform: scale(1.08);
}
</style>
