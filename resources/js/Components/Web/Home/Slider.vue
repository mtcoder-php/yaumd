<template>
    <div class="container py-4 px-4">
        <Swiper
            :modules="modules"
            :slides-per-view="1"
            :loop="true"
            :autoplay="{ delay: 3000, disableOnInteraction: false }"
            :pagination="{ dynamicBullets: true }"
            :navigation="true"
            class="slider rounded-md"
        >
            <SwiperSlide v-for="(slide, i) in slideImages" :key="i">
                <img :src="slide" alt="Slider Banner" class="w-full" draggable="false" />
            </SwiperSlide>
        </Swiper>
    </div>
</template>

<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Navigation, Pagination, Autoplay } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/navigation'
import 'swiper/css/pagination'

defineProps({
    slides: { type: Array, default: () => [] },
    lang:   { type: String, default: 'uz' },
})

const modules = [Navigation, Pagination, Autoplay]

const slideImages = [
    '/sliders/slide1.jpg',
    '/sliders/slide2.jpg',
    '/sliders/slide3.jpg',
    '/sliders/slide4.jpg',
]
</script>

<style>
.slider .swiper-button-prev,
.slider .swiper-button-next {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
    border-radius: 100%;
    width: 30px;
    height: 30px;
    min-width: 30px;
    box-shadow: 0 2px 13px 0 rgba(0, 0, 0, .15);
}

.slider .swiper-button-prev:hover,
.slider .swiper-button-next:hover { background: #0f3460; }

.swiper-button-prev:hover, .swiper-button-next:hover {
    ::slotted(svg), svg {
        color: #fff;
    }
}
.slider .swiper-pagination-bullet,
.slider .swiper-pagination-bullet-active-next { background-color: #fff !important; }

.slider .swiper-button-prev::slotted(svg),
.slider .swiper-button-next::slotted(svg) { font-size: 15px !important; color: #000; }

.slider .swiper-slide img {
    max-height: 400px;
}
.swiper-button-prev, .swiper-button-next {
    ::slotted(svg), svg {
        width: 50%;
        height: 50%;
        object-fit: contain;
        transform-origin: center;
        fill: currentColor;
        pointer-events: none;
    }
}
@media (max-width: 768px) {
    .slider .swiper-slide img {
        height: 200px;
        max-height: 250px;
    }
}
</style>
