<template>
    <section class="py-4 md:py-16 px-4 bg-white flex items-center justify-center">
        <div class="container mx-auto">
            <Link
                href="/qabul/ariza"
                class="banner-link group block relative overflow-hidden rounded-xl h-16 md:h-28"
            >
                <!-- Main background -->
                <div
                    class="absolute inset-0 bg-cover bg-center bg-no-repeat transition-transform duration-700"
                    :style="{ backgroundImage: `url(${backgroundImage})` }"
                />

                <!-- Left pull effect -->
                <div
                    class="banner-left absolute left-0 top-0 w-1/3 h-full bg-cover bg-no-repeat transition-all duration-700 ease-out"
                    :style="{ backgroundImage: `url(${backgroundImage})`, backgroundPosition: 'left center' }"
                />

                <!-- Right pull effect -->
                <div
                    class="banner-right absolute right-0 top-0 w-1/3 h-full bg-cover bg-no-repeat transition-all duration-700 ease-out"
                    :style="{ backgroundImage: `url(${backgroundImage})`, backgroundPosition: 'right center' }"
                />

                <!-- Dark overlay -->
                <div class="banner-overlay absolute inset-0 bg-black/30 transition-all duration-300" />

                <!-- Shine effect -->
                <div class="banner-shine absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/10 to-transparent skew-x-12 transition-transform duration-1000 ease-out" />

                <!-- Matn -->
                <div class="relative z-10 h-full flex items-center justify-center text-center px-4 md:px-6">
                    <div class="text-white">
                        <p
                            class="banner-title text-sm md:text-2xl font-bold mb-0 md:mb-1 tracking-wider transition-all duration-300"
                            style="text-shadow: 2px 2px 4px rgba(0,0,0,0.8)"
                        >
                            {{ title }}
                        </p>
                        <p
                            class="hidden md:block text-sm md:text-base font-medium opacity-95"
                            style="text-shadow: 1px 1px 3px rgba(0,0,0,0.7)"
                        >
                            {{ description }}
                        </p>
                        <p
                            class="hidden md:block text-xs text-white/90 mt-1"
                            style="text-shadow: 1px 1px 3px rgba(0,0,0,0.7)"
                        >
                            {{ highlightText }}
                        </p>
                    </div>
                </div>
            </Link>
        </div>
    </section>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

defineProps({
    title:         { type: String, default: "Qabul jarayoni boshlandi!" },
    description:   { type: String, default: "Bugun ro'yxatdan o'tib, maxsus imtiyozlarni qo'lga kiriting" },
    highlightText: { type: String, default: "Joylar soni chegaralangan — imkoniyatni qo'ldan boy bermang!" },
    backgroundImage: { type: String, default: "/banners/offer-banner.jpg" },
})
</script>

<style scoped>
.banner-link:hover .banner-left {
    transform: translateX(-2rem) scale(1.05);
}

.banner-link:hover .banner-right {
    transform: translateX(2rem) scale(1.05);
}

.banner-link:hover .banner-overlay {
    background-color: rgba(0, 0, 0, 0.15);
}

.banner-link:hover .banner-title {
    transform: scale(1.05);
    color: #fff;
}

.banner-link:hover .banner-shine {
    transform: translateX(100%) skewX(12deg);
}
</style>
