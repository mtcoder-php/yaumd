<template>
    <div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 items-start">

            <!-- Matn -->
            <div>
                <span class="inline-flex items-center gap-2 text-brand-600 text-xs font-bold  tracking-wide mb-3">
                    <Icon icon="mdi:camera-outline" class="w-6 h-6" />
                    Universitet haqida
                </span>
                <h2 class="text-2xl md:text-3xl font-extrabold text-brand-900 leading-snug mb-4">
                    Bizning tariximiz — yangi imkoniyatlar sari
                </h2>
                <p class="text-navy-400 text-sm md:text-base leading-relaxed mb-7">
                    Yangi Asr Universiteti — O'zbekistonda zamonaviy ta'lim, ilm-fan va innovatsiyalarni birlashtirgan nufuzli oliy ta'lim muassasasi. Bizning maqsadimiz — yuksak bilimli, raqobatbardosh va tashabbuskor yoshlarni tarbiyalash.
                </p>
                <div class="flex flex-wrap items-center gap-3">
                    <Link href="/universitet/haqida/umumiy" class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold transition">
                        Batafsil ma'lumot
                        <Icon icon="mdi:arrow-right" class="w-4 h-4" />
                    </Link>
                    <button
                        type="button"
                        @click="videoOpen = true"
                        class="inline-flex items-center gap-2 px-5 py-3 rounded-xl border border-gray-200 text-brand-900 text-sm font-semibold hover:border-brand-300 hover:text-brand-600 transition"
                    >
                        <Icon icon="mdi:play-circle-outline" class="w-4 h-4" />
                        Video taqdimot
                    </button>
                </div>
            </div>

            <!-- Video/rasm -->
            <div>
                <button
                    type="button"
                    @click="videoOpen = true"
                    class="relative block w-full rounded-2xl overflow-hidden group"
                    style="height: 320px"
                >
                    <!-- MUHIM: vaqtinchalik surat — /public/sliders/slide1.jpg. Haqiqiy
                         "Universitet haqida" video/surat tayyor bo'lgach shu manzilni
                         almashtirish kifoya. -->
                    <img
                        src="/sliders/slide2.png"
                        alt="Universitet haqida"
                        class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                    >
                    <div
                        class="absolute inset-0"
                        style="background: linear-gradient(0deg, rgba(11,19,48,0.78) 0%, rgba(11,19,48,0.2) 45%, rgba(11,19,48,0.05) 100%);"
                    ></div>

                    <!-- Play tugma + izoh matni -->
                    <span class="absolute left-4 bottom-4 flex items-center gap-3">
                        <span class="flex-shrink-0 w-11 h-11 rounded-full bg-brand-600 group-hover:bg-brand-500 flex items-center justify-center shadow-lg transition">
                            <Icon icon="mdi:play" class="w-5 h-5 text-white ml-0.5" />
                        </span>
                        <span class="text-left">
                            <span class="block text-white text-sm font-semibold leading-tight">Universitet haqida</span>
                            <span class="block text-white/70 text-xs leading-tight mt-0.5">video tomosha qiling</span>
                        </span>
                    </span>

                    <!-- Davomiylik -->
                    <span class="absolute right-4 bottom-4 text-white/80 text-xs font-medium">03:24</span>
                </button>
            </div>

        </div>

        <!-- Video modal -->
        <div
            v-if="videoOpen"
            @click.self="videoOpen = false"
            class="fixed inset-0 z-[60] bg-navy-950/80 flex items-center justify-center p-4"
        >
            <div class="relative w-full max-w-2xl aspect-video bg-black rounded-xl overflow-hidden flex items-center justify-center">
                <button
                    type="button"
                    @click="videoOpen = false"
                    class="absolute top-3 right-3 w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition"
                >
                    <Icon icon="mdi:close" class="w-5 h-5" />
                </button>
                <p class="text-white/60 text-sm">Video taqdimot tez orada qo'shiladi</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

const videoOpen = ref(false)
</script>
