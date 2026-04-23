<template>
    <section class="container mx-auto px-4 pt-4">
        <div class="relative rounded-2xl overflow-hidden slider-height">

            <!-- Slides -->
            <div
                class="flex h-full"
                :style="{
                    transform: `translateX(-${current * 100}%)`,
                    transition: transitioning ? 'transform 0.6s cubic-bezier(0.77,0,0.18,1)' : 'none'
                }"
            >
                <div
                    v-for="(slide, i) in slideImages"
                    :key="i"
                    class="relative flex-shrink-0 w-full h-full"
                >
                    <img
                        :src="slide"
                        alt="Slider"
                        class="absolute inset-0 w-full h-full object-cover"
                        draggable="false"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-black/40 via-transparent to-transparent"></div>
                </div>
            </div>

            <!-- Prev -->
            <button
                @click="prev"
                class="absolute left-3 top-1/2 -translate-y-1/2 z-8 w-8 h-8 rounded-full bg-white/25 hover:bg-white/50 backdrop-blur-sm border border-white/30 flex items-center justify-center transition-all hover:scale-110"
            >
                <Icon icon="mdi:chevron-left" class="w-6 h-6 text-white" />
            </button>

            <!-- Next -->
            <button
                @click="next"
                class="absolute right-3 top-1/2 -translate-y-1/2 z-8 w-8 h-8 rounded-full bg-white/25 hover:bg-white/50 backdrop-blur-sm border border-white/30 flex items-center justify-center transition-all hover:scale-110"
            >
                <Icon icon="mdi:chevron-right" class="w-6 h-6 text-white" />
            </button>

            <!-- Dots -->
            <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-10 flex items-center gap-2">
                <button
                    v-for="(_, i) in slideImages"
                    :key="i"
                    @click="goTo(i)"
                    class="rounded-full transition-all duration-300"
                    :class="current === i ? 'w-6 h-2 bg-white' : 'w-2 h-2 bg-white/50 hover:bg-white/80'"
                />
            </div>

            <!-- Progress bar -->
            <div class="absolute bottom-0 left-0 right-0 z-10 h-0.5 bg-white/20">
                <div
                    class="h-full bg-white"
                    :style="{
                        width: progress + '%',
                        transition: playing ? `width ${delay}ms linear` : 'none'
                    }"
                />
            </div>

        </div>
    </section>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Icon } from '@iconify/vue'

const slideImages = [
    '/sliders/slide1.jpg',
    '/sliders/slide2.jpg',
    '/sliders/slide3.jpg',
    '/sliders/slide4.jpg',
]

const delay         = 4000
const current       = ref(0)
const transitioning = ref(true)
const progress      = ref(0)
const playing       = ref(false)
let timer           = null

const next = () => {
    transitioning.value = true
    current.value = (current.value + 1) % slideImages.length
    resetProgress()
}

const prev = () => {
    transitioning.value = true
    current.value = (current.value - 1 + slideImages.length) % slideImages.length
    resetProgress()
}

const goTo = (i) => {
    transitioning.value = true
    current.value = i
    resetProgress()
}

const resetProgress = () => {
    playing.value = false
    progress.value = 0
    clearInterval(timer)
    setTimeout(() => {
        playing.value = true
        progress.value = 100
        timer = setInterval(next, delay)
    }, 50)
}

onMounted(() => resetProgress())
onUnmounted(() => clearInterval(timer))
</script>

<style scoped>
.slider-height {
    height: 460px;
}

@media (max-width: 1024px) {
    .slider-height {
        height: 360px;
    }
}

@media (max-width: 768px) {
    .slider-height {
        height: 260px;
    }
}

@media (max-width: 480px) {
    .slider-height {
        height: 200px;
    }
}
</style>
