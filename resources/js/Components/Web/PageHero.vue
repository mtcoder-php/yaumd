<template>
    <section class="relative overflow-hidden min-h-[280px] md:min-h-[305px] bg-slate-100">

        <!-- Fon rasmi — right side edge-to-edge -->
        <div v-if="image" class="absolute inset-0 z-0">
            <img :src="image" :alt="title" class="w-full h-full object-cover object-[65%_center]">
        </div>
        <div v-else class="absolute inset-0 z-0 bg-slate-200"></div>

        <!-- Chap tomondagi INDIGO tusli keskin qiya panel -->
        <div class="hero-soft-panel"></div>

        <!-- Chap tomondagi naqshlar (indigo/violet tusda) -->
        <div class="absolute inset-0 z-[3] pointer-events-none overflow-hidden" aria-hidden="true">
            <span class="absolute -left-12 bottom-[-20px] w-48 h-48 rounded-3xl bg-indigo-400/30 rotate-45 blur-sm"></span>
            <span class="absolute -left-4 top-6 w-28 h-28 rounded-2xl bg-indigo-300/30 rotate-45"></span>
            <span class="absolute left-20 -bottom-8 w-32 h-32 rounded-2xl border-2 border-indigo-300/40 rotate-45"></span>
        </div>

        <div class="relative z-[4] container mx-auto px-4 py-10 md:py-14">

            <!-- Breadcrumb -->
            <nav class="flex items-center flex-wrap gap-1.5 text-sm text-indigo-700/80 mb-6">
                <template v-for="(crumb, i) in crumbs" :key="i">
                    <Icon v-if="i > 0" icon="mdi:chevron-right" class="w-4 h-4 text-indigo-400 flex-shrink-0" />
                    <Link
                        v-if="crumb.href"
                        :href="crumb.href"
                        class="hover:text-indigo-900 transition font-medium"
                    >
                        {{ crumb.label }}
                    </Link>
                    <span
                        v-else
                        class="font-semibold"
                        :class="i === crumbs.length - 1 ? 'text-indigo-950' : 'text-indigo-700/80'"
                    >
                        {{ crumb.label }}
                    </span>
                </template>
            </nav>

            <div class="max-w-xl">
                <h1 class="text-3xl md:text-4xl lg:text-[42px] font-bold text-indigo-950 leading-tight mb-4">
                    {{ title }}
                </h1>
                <p v-if="subtitle" class="text-indigo-900/75 text-base md:text-lg leading-relaxed font-normal">
                    {{ subtitle }}
                </p>
            </div>
        </div>

        <!-- Suzuvchi badge -->
        <div
            v-if="badge"
            class="absolute z-[4] bottom-4 right-4 md:right-8 flex items-center gap-2.5 bg-white/95 backdrop-blur-sm rounded-2xl shadow-lg border border-indigo-50 pl-3 pr-5 py-3"
        >
            <span class="flex-shrink-0 w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center">
                <Icon :icon="badge.icon || 'mdi:school-outline'" class="w-5 h-5 text-indigo-600" />
            </span>
            <div class="leading-tight">
                <p class="text-indigo-950 font-bold text-lg">{{ badge.value }}</p>
                <p class="text-gray-500 text-[11px] font-medium">{{ badge.label }}</p>
            </div>
        </div>

    </section>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

defineProps({
    // [{ label, href? }]
    crumbs:   { type: Array, default: () => [] },
    title:    { type: String, required: true },
    subtitle: { type: String, default: '' },
    image:    { type: String, default: '' },
    // { icon, value, label }
    badge:    { type: Object, default: null },
})
</script>

<style scoped>
/* Indigo fon va o'ng chegarani keskin qiya qilish */
.hero-soft-panel {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    width: 68%;
    z-index: 2;
    pointer-events: none;

    /* Indigo tusdagi gradient fon */
    background: linear-gradient(115deg, #eef2ff 0%, #e0e7ff 65%, rgba(224, 231, 255, 0.85) 100%);

    /* Chegarani keskin va qiya qilib kesish */
    clip-path: polygon(0 0, 100% 0, 82% 100%, 0 100%);
}

/* Ekran o'lchamlariga qarab panel kengligi */
@media (min-width: 640px) {
    .hero-soft-panel {
        width: 62%;
        clip-path: polygon(0 0, 100% 0, 80% 100%, 0 100%);
    }
}
@media (min-width: 768px) {
    .hero-soft-panel {
        width: 58%;
        clip-path: polygon(0 0, 100% 0, 78% 100%, 0 100%);
    }
}
@media (min-width: 1024px) {
    .hero-soft-panel {
        width: 52%;
        clip-path: polygon(0 0, 100% 0, 75% 100%, 0 100%);
    }
}
</style>
