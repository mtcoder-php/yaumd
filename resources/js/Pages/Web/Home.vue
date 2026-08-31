<template>
    <WebLayout :settings="settings" :faculties="faculties">

        <!-- Slider -->

            <Slider :slides="sliders" :lang="lang" />


        <!-- Qolganlar -->
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
            { label: "Talabalar",     value: "1000+" },
            { label: "O'qituvchilar", value: "100+"  },
            { label: "Yo'nalishlar",  value: "20+"   },
            { label: "Hamkorlar",     value: "50+"   },
        ]
    },
})

const lang = computed(() => localStorage.getItem('lang') || 'uz')
</script>
