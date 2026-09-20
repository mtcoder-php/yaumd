<template>
    <WebLayout :settings="settings">
        <Slider :slides="sliders" :lang="lang" />
        <QuickLinks />

        <!-- MUHIM: "Bizning tariximiz" dan "Hamkorlar" (Partners) gacha bo'lgan
             asosiy kontent va o'ng tomondagi Rightbar shu yerda YAGONA grid
             ichida, bitta rightbar sifatida birlashtirilgan (har bir bo'lim
             o'zining alohida sidebar'ini emas). Partners esa to'liq kenglikda,
             shu grid'dan tashqarida qoladi. -->
        <section class="py-14 md:py-16 bg-white">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-1 lg:grid-cols-9 gap-8 items-start">
                    <div class="lg:col-span-7 space-y-14 md:space-y-16">
                        <Mission />
                        <News :news="news" :lang="lang" />
                        <Departments :departments="departments" :lang="lang" />
                        <EventsAndNews :news="news" />
                    </div>
                    <div class="lg:col-span-2">
                        <Rightbar />
                    </div>
                </div>
            </div>
        </section>

        <Partners :partners="partners" />
    </WebLayout>
</template>

<script setup>
import { computed } from 'vue'
import Slider from '@/Components/Web/Home/Slider.vue'
import QuickLinks from '@/Components/Web/Home/QuickLinks.vue'
import Mission from '@/Components/Web/Home/Mission.vue'
import News from '@/Components/Web/Home/News.vue'
import Departments from '@/Components/Web/Home/Departments.vue'
import EventsAndNews from '@/Components/Web/Home/EventsAndNews.vue'
import Rightbar from '@/Components/Web/Home/Rightbar.vue'
import Partners from '@/Components/Web/Home/Partners.vue'
import WebLayout from '@/Layouts/WebLayout.vue'

defineProps({
    sliders:    { type: Array, default: () => [] },
    news:       { type: Array, default: () => [] },
    directions: { type: Array, default: () => [] },
    partners:   { type: Array, default: () => [] },
    quickLinks:  { type: Array, default: () => [] },
    departments: { type: Array, default: () => [] },
    settings:    { type: Object, default: () => ({}) },
})

const lang = computed(() => localStorage.getItem('lang') || 'uz')
</script>
