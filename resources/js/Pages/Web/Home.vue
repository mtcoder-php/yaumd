<template>
    <WebLayout :settings="settings" :faculties="faculties">

        <!-- Slider -->
        <Slider :slides="sliders" :lang="lang" />
        <!-- QuickLinks -->
        <QuickLinks />
        <Directions />
        <Stats />
        <News />
        <Partners />
        <Mission />
        <AdmissionBanner />
    </WebLayout>
</template>

<script setup>
import { computed } from 'vue'
import Slider from '@/Components/Web/Home/Slider.vue'
import QuickLinks from '@/Components/Web/Home/QuickLinks.vue'
import Directions from '@/Components/Web/Home/Directions.vue'
import Stats from '@/Components/Web/Home/Stats.vue'
import News from '@/Components/Web/Home/News.vue'
import Partners from '@/Components/Web/Home/Partners.vue'
import Mission from '@/Components/Web/Home/Mission.vue'
import AdmissionBanner from '@/Components/Web/Home/AdmissionBanner.vue'
import { Navigation, Pagination, Autoplay } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'
import WebLayout from '@/Layouts/WebLayout.vue'


const props = defineProps({
    sliders:    { type: Array, default: () => [] },
    news:       { type: Array, default: () => [] },
    directions: { type: Array, default: () => [] },
    partners:   { type: Array, default: () => [] },
    quickLinks: { type: Array, default: () => [] },
    faculties:  { type: Array, default: () => [] },
    settings:   { type: Object, default: () => ({}) },
    stats: {
        type: Array,
        default: () => [
            { label: "Talabalar", value: "1000+" },
            { label: "O'qituvchilar", value: "100+" },
            { label: "Yo'nalishlar", value: "20+" },
            { label: "Hamkorlar", value: "50+" },
        ]
    },
})

const swiperModules = [Navigation, Pagination, Autoplay]
const lang = computed(() => localStorage.getItem('lang') || 'uz')

const formatDate = (date) => {
    if (!date) return ''
    return new Date(date).toLocaleDateString('uz-UZ', {
        day: '2-digit', month: '2-digit', year: 'numeric'
    })
}
</script>
