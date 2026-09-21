<template>
    <section class="relative overflow-hidden min-h-[280px] md:min-h-[305px]">

        <!-- Fon rasmi — butun bo'lim kengligiga to'liq (edge-to-edge).
             object-[65%_center]: rasmning o'ng tomoni (masalan bino)
             ko'proq ko'rinib turishi uchun fokus nuqtasi o'ngga suriladi. -->
        <div v-if="image" class="absolute inset-0 z-0">
            <img :src="image" :alt="title" class="w-full h-full object-cover object-[65%_center]">
        </div>
        <div v-else class="absolute inset-0 z-0 bg-gradient-to-br from-navy-50 via-white to-brand-50"></div>

        <!-- Chap tomondagi YUMSHOQ oq panel — endi alohida "aniq chiziq"
             yoki "blur nusxa" qatlamlari yo'q, bitta panel bo'lib, chegarasi
             faqat linear-gradient (oq → shaffof) orqali erib boradi, ustiga
             juda yengil skew bilan ozgina qiyalik beriladi. Kengligi
             breakpoint bo'yicha o'zgaradi (mobil-da matn uchun kengroq, katta
             ekranlarda torroq — rasm ko'proq ochiladi), lekin gradient
             nisbatlari bir xil qoladi (pastdagi <style>ga qarang). -->
        <div class="hero-soft-panel"></div>

        <!-- Fon naqshi — aylanma (rotated) kvadratlar, oq panel ustida
             ko'rinadigan, matnga xalaqit bermasligi uchun juda past
             shaffoflikda (pointer-events yo'q). -->
        <div class="absolute inset-0 z-[3] pointer-events-none overflow-hidden" aria-hidden="true">
            <span class="absolute -left-10 top-10 w-40 h-40 rounded-3xl bg-brand-200/40 rotate-45"></span>
            <span class="absolute left-24 -top-6 w-24 h-24 rounded-2xl bg-navy-200/30 rotate-45"></span>
            <span class="absolute -left-6 bottom-0 w-28 h-28 rounded-2xl border-2 border-brand-200/50 rotate-45"></span>
        </div>

        <div class="relative z-[4] container mx-auto px-4 py-10 md:py-14">

            <!-- Breadcrumb -->
            <nav class="flex items-center flex-wrap gap-1.5 text-sm text-gray-500 mb-6">
                <template v-for="(crumb, i) in crumbs" :key="i">
                    <Icon v-if="i > 0" icon="mdi:chevron-right" class="w-4 h-4 text-gray-300 flex-shrink-0" />
                    <Link
                        v-if="crumb.href"
                        :href="crumb.href"
                        class="hover:text-brand-600 transition font-medium"
                    >
                        {{ crumb.label }}
                    </Link>
                    <span
                        v-else
                        class="font-semibold"
                        :class="i === crumbs.length - 1 ? 'text-navy-900' : 'text-gray-500'"
                    >
                        {{ crumb.label }}
                    </span>
                </template>
            </nav>

            <div class="max-w-xl">
                <h1 class="text-3xl md:text-4xl lg:text-[42px] font-bold text-navy-900 leading-tight mb-4">
                    {{ title }}
                </h1>
                <p v-if="subtitle" class="text-gray-500 text-base md:text-lg leading-relaxed">
                    {{ subtitle }}
                </p>
            </div>
        </div>

        <!-- Suzuvchi badge (masalan "20+ Yillik tajriba") — rasmning ko'rinib
             turgan (o'ng) qismi ustida, pastki-o'ng burchakda. -->
        <div
            v-if="badge"
            class="absolute z-[4] bottom-4 right-4 md:right-8 flex items-center gap-2.5 bg-white rounded-xl shadow-xl border border-gray-100 pl-2.5 pr-4 py-2.5"
        >
            <span class="flex-shrink-0 w-9 h-9 rounded-lg bg-brand-100 flex items-center justify-center">
                <Icon :icon="badge.icon || 'mdi:school-outline'" class="w-4 h-4 text-brand-600" />
            </span>
            <div class="leading-tight">
                <p class="text-navy-900 font-bold text-base">{{ badge.value }}</p>
                <p class="text-gray-400 text-[11px]">{{ badge.label }}</p>
            </div>
        </div>

    </section>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

defineProps({
    // [{ label, href? }] — href bo'lmasa oddiy matn (odatda joriy sahifa) sifatida chiqadi
    crumbs:   { type: Array, default: () => [] },
    title:    { type: String, required: true },
    subtitle: { type: String, default: '' },
    image:    { type: String, default: '' },
    // { icon, value, label } — masalan { icon: 'mdi:school-outline', value: '20+', label: 'Yillik tajriba' }
    badge:    { type: Object, default: null },
})
</script>

<style scoped>
/* Yagona yumshoq oq panel — hech qanday clip-path yoki blur nusxa yo'q,
   faqat bitta linear-gradient (oq → shaffof) + juda yengil skewX orqali
   rasmga tabiiy, "erib ketuvchi" o'tish hosil qilinadi. Gradient
   nisbatlari (%) panelning O'ZIGA nisbatan hisoblanadi, shuning uchun
   breakpointlar orasida faqat KENGLIK o'zgaradi — gradient qiymatlari
   bir xil qoladi va avtomatik moslashadi. */
.hero-soft-panel {
    position: absolute;
    top: 0;
    bottom: 0;
    z-index: 2;
    pointer-events: none;

    /* MUHIM: chap chekka 0 emas, -60px'dan boshlanadi (kenglikka ham
       xuddi shuncha piksel qo'shilgan). Sababi: skewX(-5deg) elementni
       "left center" atrofida qiyalatganda, markazdan uzoqroq
       (yuqori/pastki) qatorlar chap chetdan bir necha o'nlab piksel
       ICHKARIGA siljib qoladi — agar panel aynan x:0'dan boshlansa, bu
       yuqori-chap (yoki pastki-chap) burchakda oq fon "yetib
       bormay", ostidagi rasm tirqish bo'lib ko'rinib qolardi (Playwright
       skrinshotida aniqlangan haqiqiy xato). -60px zaxira bilan bu
       tirqish butunlay yo'qoladi, section'dagi overflow-hidden esa ortiqcha
       chiqib turgan qismni kesib tashlaydi — o'ng chetdagi ko'rinish
       o'zgarmaydi. */
    left: -60px;
    width: calc(90% + 60px);

    /* Chegara endi biroz "bilinib" turadi: 60-68% oralig'ida juda yengil
       kulrang soya chizig'i (rgba(0,0,0,..)) qo'shildi — bu xuddi
       qatlangan qog'ozning ingichka soyasidek, qiya chiziqni ko'zga
       aniqroq tashlaydi, lekin baribir qattiq/keskin chiziq emas, faqat
       gradient ichidagi bitta nozik urg'u. Undan keyin oddiy tarzda
       shaffoflikka o'tadi. */
    background: linear-gradient(
        100deg,
        rgba(255, 255, 255, 1) 0%,
        rgba(255, 255, 255, 1) 58%,
        rgba(255, 255, 255, 0.92) 64%,
        rgba(0, 0, 0, 0.07) 68%,
        rgba(255, 255, 255, 0.5) 73%,
        rgba(255, 255, 255, 0.18) 82%,
        rgba(255, 255, 255, 0) 92%
    );

    transform: skewX(-5deg);
    transform-origin: left center;
}

@media (min-width: 640px) {
    .hero-soft-panel { width: calc(84% + 60px); }
}
@media (min-width: 768px) {
    .hero-soft-panel { width: calc(78% + 60px); }
}
@media (min-width: 1024px) {
    .hero-soft-panel { width: calc(72% + 60px); }
}
</style>
