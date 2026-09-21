<template>
    <WebLayout :settings="settings">
        <PageHero
            :crumbs="[
                { label: 'Asosiy', href: '/' },
                { label: 'Universitet' },
                { label: 'Universitet haqida' },
            ]"
            title="Universitet haqida"
            subtitle="Yangi Asr Universiteti — zamonaviy ta'lim, ilm-fan va innovatsiyalar orqali jamiyat rivojiga hissa qo'shadi."
            image="/sliders/slide2.png"
            :badge="{ icon: 'mdi:school-outline', value: '20+', label: 'Yillik tajriba' }"
        />

        <section class="py-10 md:py-12 bg-white">
            <div class="container mx-auto px-4">
                <div class="flex flex-col lg:flex-row gap-8 items-start">
                    <AboutSidebar active-href="/university/about/general" />

                    <div class="flex-1 min-w-0 bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8">
                        <AboutOverview />
                    </div>
                </div>
            </div>
        </section>
    </WebLayout>
</template>

<script setup>
import WebLayout from '@/Layouts/WebLayout.vue'
import PageHero from '@/Components/Web/PageHero.vue'
import AboutSidebar from '@/Components/Web/University/AboutSidebar.vue'
import AboutOverview from '@/Components/Web/University/AboutOverview.vue'

defineProps({
    settings: { type: Object, default: () => ({}) },
})
</script>
