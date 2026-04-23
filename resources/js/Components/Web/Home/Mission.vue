<template>
    <section class="py-12 md:py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-3 md:grid-cols-3 gap-8">

                <div
                    v-for="item in missions"
                    :key="item.title"
                    class="flex flex-col items-center justify-center text-center group cursor-pointer py-6"
                    @mouseenter="hovered = item.title"
                    @mouseleave="hovered = null"
                >
                    <Icon
                        :icon="item.icon"
                        class="mb-4 transition-all duration-500 group-hover:-translate-y-2 ease-out"
                        :style="{
                            width: '72px',
                            height: '72px',
                            color: hovered === item.title ? item.color : '#374151',
                            filter: hovered === item.title ? `drop-shadow(0 4px 16px ${item.color}55)` : 'none',
                            transition: 'color 0.3s, filter 0.3s, transform 0.5s'
                        }"
                    />
                    <h3
                        class="text-lg font-bold mb-1.5 transition-colors duration-300"
                        :style="{ color: hovered === item.title ? item.color : '#1f2937' }"
                    >
                        {{ item.title }}
                    </h3>
                    <p class="text-sm text-gray-500">
                        {{ item.desc }}
                    </p>
                </div>

            </div>
        </div>
    </section>
</template>

<script setup>
import { ref } from 'vue'
import { Icon } from '@iconify/vue'

const hovered = ref(null)

const missions = [
    {
        icon: 'mdi:cast-education',
        title: "Ta'lim",
        desc: "Intellektual salohiyat",
        color: '#4c1d95',
    },
    {
        icon: 'mdi:hand-heart-outline',
        title: "Tarbiya",
        desc: "Axloqiy kamolot asosi",
        color: '#4c1d95',
    },
    {
        icon: 'mdi:cog-outline',
        title: "Tajriba",
        desc: "Bilimning amaliy tasdig'i",
        color: '#4c1d95',
    },
]
</script>
