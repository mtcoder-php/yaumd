<template>
    <section class="relative overflow-hidden min-h-[280px] md:min-h-[305px] bg-slate-100">

        <!-- Fon rasmi — right side edge-to-edge -->
        <div v-if="image" class="absolute inset-0 z-0">
            <img :src="image" :alt="title" class="w-full h-full object-cover object-[65%_center]">
        </div>
        <div v-else class="absolute inset-0 z-0 bg-slate-200"></div>

        <!-- Chap tomondagi INDIGO tusli keskin qiya panel -->
        <div class="hero-soft-panel"></div>

        <!-- Panel chegarasidan rasm ustiga o'tuvchi "tuman" — chiziq aniq
             bilinib turadi, lekin darhol keskin tugamaydi: shu yerdan
             boshlab bir necha o'n piksel davomida asta-sekin shaffoflashib,
             rasm ustida hech narsiz eriydi. -->
        <div class="hero-fog"></div>

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

            <!-- MUHIM: "withPhoto" faqat shaxs profili kabi sahifalarda
                 (masalan StaffProfile.vue) beriladi — About/Staff kabi
                 boshqa sahifalar bu propni bermaydi, shuning uchun ularda
                 hech narsa o'zgarmaydi (orqaga mos, additive o'zgarish). -->
            <div class="flex flex-col sm:flex-row gap-5 sm:items-center">
                <div
                    v-if="withPhoto"
                    class="w-28 h-32 md:w-32 md:h-36 rounded-2xl overflow-hidden border-4 border-white shadow-lg flex-shrink-0 bg-indigo-100"
                >
                    <img v-if="photo" :src="photo" :alt="title" class="w-full h-full object-cover">
                    <div v-else class="w-full h-full flex items-center justify-center">
                        <Icon icon="mdi:account" class="w-12 h-12 text-indigo-300" />
                    </div>
                </div>

                <div class="max-w-xl">
                    <h1 class="text-3xl md:text-4xl lg:text-[42px] font-bold text-indigo-950 leading-tight mb-4">
                        {{ title }}
                    </h1>
                    <p v-if="subtitle" class="text-indigo-900/75 text-base md:text-lg leading-relaxed font-normal">
                        {{ subtitle }}
                    </p>
                </div>
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
    // Shaxs profili sahifalari uchun (masalan StaffProfile.vue) — sarlavha
    // yonida kichik rasm kartasi. withPhoto=false bo'lsa (standart),
    // boshqa barcha sahifalarda hech narsa o'zgarmaydi.
    withPhoto: { type: Boolean, default: false },
    photo:     { type: String, default: null },
})
</script>

<style scoped>
/* Indigo fon va o'ng chegarani keskin qiya qilish. --panel-w / --panel-clip
   orqali .hero-fog bilan bir xil "koordinata tizimi"ni bo'lishadi, shuning
   uchun tuman har doim aynan chiziq ustida boshlanadi. */
.hero-soft-panel {
    position: absolute;
    top: 0;
    bottom: 0;
    left: 0;
    z-index: 2;
    pointer-events: none;

    --panel-w: 68%;
    --panel-clip: 0.82;
    width: var(--panel-w);

    /* Indigo tusdagi gradient fon */
    background: linear-gradient(115deg, #eef2ff 0%, #e0e7ff 65%, rgba(224, 231, 255, 0.85) 100%);

    /* Chegarani keskin va qiya qilib kesish */
    clip-path: polygon(0 0, 100% 0, calc(var(--panel-clip) * 100%) 100%, 0 100%);
}

/* Chiziqdan boshlab rasm ustiga cho'zilib boruvchi tuman qatlami.
   left/width panelning O'ZI bilan bir xil --panel-w/--panel-clip
   o'zgaruvchilaridan hisoblanadi: left = panel qiyaligining eng chap
   nuqtasi (pastki burchagi), width = shu nuqtadan panelning eng o'ng
   uchigacha bo'lgan farq + 60px qo'shimcha "erish" zaxirasi. Natijada
   qattiq chiziq bilinib turadi, lekin darhol tugamay, asta-sekin
   shaffoflashib rasm ustida yo'qolib ketadi. */
.hero-fog {
    position: absolute;
    top: 0;
    bottom: 0;
    z-index: 2;
    pointer-events: none;

    --panel-w: 68%;
    --panel-clip: 0.82;
    left: calc(var(--panel-w) * var(--panel-clip));
    width: calc(var(--panel-w) * (1 - var(--panel-clip)) + 60px);

    background: linear-gradient(
        to right,
        rgba(224, 231, 255, 0.65) 0%,
        rgba(224, 231, 255, 0.38) 35%,
        rgba(224, 231, 255, 0.14) 65%,
        rgba(224, 231, 255, 0) 100%
    );
}

/* Ekran o'lchamlariga qarab panel kengligi */
@media (min-width: 640px) {
    .hero-soft-panel { --panel-w: 62%; --panel-clip: 0.80; }
    .hero-fog        { --panel-w: 62%; --panel-clip: 0.80; }
}
@media (min-width: 768px) {
    .hero-soft-panel { --panel-w: 58%; --panel-clip: 0.78; }
    .hero-fog        { --panel-w: 58%; --panel-clip: 0.78; }
}
@media (min-width: 1024px) {
    .hero-soft-panel { --panel-w: 52%; --panel-clip: 0.75; }
    .hero-fog        { --panel-w: 52%; --panel-clip: 0.75; }
}
</style>
