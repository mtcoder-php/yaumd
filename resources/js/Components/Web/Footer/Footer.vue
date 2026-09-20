<template>
    <footer class="bg-navy-900">

        <!-- Footer top -->
        <div class="py-14 px-4 border-b border-white/10">
            <div class="container mx-auto">
                <div class="flex flex-col lg:flex-row lg:items-start gap-10 lg:gap-12">

                    <!-- Logo + tavsif — kattaroq, konteyner kengligiga nisbatan foizda
                         (aniq piksel emas), shu bois har xil ekran o'lchamida ham
                         joylar bir-birining ustiga chiqib ketmaydi -->
                    <div class="lg:basis-[27%] lg:shrink-0">
                        <div class="mb-5">
                            <img src="/assets/logo-white.png" alt="Yangi Asr Universiteti" class="h-16 md:h-[70px] w-auto mb-2">
                            <p class="text-sm text-white/50 font-medium">Ilm, tafakkur va taraqqiyot yo'lida</p>
                        </div>
                        <p class="text-white/50 text-base leading-relaxed mb-5 max-w-sm">
                            {{ settings.description || "Zamonaviy ta'lim, ilm-fan va innovatsiyalarni birlashtirgan nufuzli oliy ta'lim muassasasi." }}
                        </p>
                        <div class="flex items-center gap-2">
                            <a
                                v-for="s in socials"
                                :key="s.name"
                                :href="s.url"
                                target="_blank"
                                :title="s.name"
                                class="w-9 h-9 rounded-full border border-white/15 flex items-center justify-center text-white/60 hover:text-white hover:bg-brand-600 hover:border-brand-600 transition-all duration-300"
                            >
                                <Icon :icon="s.icon" class="w-4 h-4" />
                            </a>
                        </div>
                    </div>

                    <!-- Tezkor havolalar / Foydali / Biz bilan bog'laning — Logo tomonga
                         yaqin, ixcham guruh sifatida (belgilangan kenglikda) -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 lg:basis-[38%] lg:shrink-0">
                        <div>
                            <h3 class="text-sm font-bold text-white mb-4">Tezkor havolalar</h3>
                            <ul class="space-y-2.5">
                                <li v-for="link in quickLinksList" :key="link.label">
                                    <Link :href="link.href" class="footer-link text-white/55 text-sm">{{ link.label }}</Link>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <h3 class="text-sm font-bold text-white mb-4">Foydali</h3>
                            <ul class="space-y-2.5">
                                <li v-for="link in usefulLinks" :key="link.label">
                                    <Link :href="link.href" class="footer-link text-white/55 text-sm">{{ link.label }}</Link>
                                </li>
                            </ul>
                        </div>

                        <div>
                            <h3 class="text-sm font-bold text-white mb-4">Biz bilan bog'laning</h3>
                            <ul class="space-y-3">
                                <li>
                                    <a :href="`mailto:${settings.email || 'info@yangiasr.uz'}`" class="flex items-center gap-2.5 text-white/55 hover:text-white text-sm transition">
                                        <Icon icon="mdi:email-outline" class="w-4 h-4 flex-shrink-0 text-brand-400" />
                                        {{ settings.email || 'info@yangiasr.uz' }}
                                    </a>
                                </li>
                                <li>
                                    <a :href="`tel:${settings.phone || '+998712345678'}`" class="flex items-center gap-2.5 text-white/55 hover:text-white text-sm transition">
                                        <Icon icon="mdi:phone-outline" class="w-4 h-4 flex-shrink-0 text-brand-400" />
                                        {{ settings.phone || '+998 71 234 56 78' }}
                                    </a>
                                </li>
                                <li class="flex items-start gap-2.5 text-white/55 text-sm">
                                    <Icon icon="mdi:map-marker-outline" class="w-4 h-4 flex-shrink-0 text-brand-400 mt-0.5" />
                                    <span>{{ settings.address || "Toshkent shahri, O'zbekiston" }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Newsletter — qolgan bo'sh joyni egallaydi, orqa fonda naqsh,
                         input va tugma bitta yaxlit shaklga yopishtirilgan -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-sm font-bold text-white mb-4">Yangiliklardan xabardor bo'ling</h3>
                        <div class="relative overflow-hidden rounded-2xl border border-white/10 bg-white/[0.03] p-5">
                            <svg class="absolute inset-0 w-full h-full opacity-[0.08] pointer-events-none" preserveAspectRatio="xMidYMid slice">
                                <defs>
                                    <pattern id="footer-newsletter-pattern" width="70" height="70" patternUnits="userSpaceOnUse">
                                        <path d="M15 55 V38 A10 10 0 0 1 35 38 V55" fill="none" stroke="white" stroke-width="1.4" />
                                        <circle cx="25" cy="24" r="2.5" fill="white" />
                                        <path d="M45 55 V42 H60 V55" fill="none" stroke="white" stroke-width="1.4" />
                                    </pattern>
                                </defs>
                                <rect width="100%" height="100%" fill="url(#footer-newsletter-pattern)" />
                            </svg>
                            <div class="relative">
                                <p v-if="!subscribed" class="text-white/50 text-sm mb-4">Eng so'nggi yangiliklar va e'lonlarni birinchilardan bo'lib oling.</p>
                                <form v-if="!subscribed" @submit.prevent="subscribe" class="flex rounded-full overflow-hidden">
                                    <input
                                        v-model="newsletterEmail"
                                        type="email"
                                        required
                                        placeholder="Email manzilingiz"
                                        class="min-w-0 flex-1 px-4 py-3 bg-white text-navy-900 placeholder-gray-400 text-sm focus:outline-none"
                                    >
                                    <button
                                        type="submit"
                                        class="flex-shrink-0 px-6 py-3 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold transition"
                                    >
                                        Obuna
                                    </button>
                                </form>
                                <p v-else class="flex items-center gap-2 text-sm text-white/80 bg-white/5 border border-white/10 rounded-lg px-3 py-2.5">
                                    <Icon icon="mdi:check-circle-outline" class="w-4 h-4 text-emerald-400 flex-shrink-0" />
                                    Obuna bo'ldingiz, rahmat!
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Footer bottom -->
        <div class="py-4 px-4">
            <div class="container mx-auto flex flex-col md:flex-row items-center justify-between gap-2 text-center md:text-left">
                <p class="text-xs text-white/40">
                    © {{ year }} Yangi Asr Universiteti. Barcha huquqlar himoyalangan.
                </p>
                <div class="flex items-center gap-5">
                    <a href="#" class="text-xs text-white/40 hover:text-white/70 transition">Maxfiylik siyosati</a>
                    <a href="#" class="text-xs text-white/40 hover:text-white/70 transition">Foydalanish shartlari</a>
                    <a href="#" class="text-xs text-white/40 hover:text-white/70 transition">Sayt xaritasi</a>
                </div>
            </div>
        </div>

    </footer>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

defineProps({
    settings: { type: Object, default: () => ({}) },
})

const year = new Date().getFullYear()

const socials = [
    { name: 'Telegram',  icon: 'mdi:telegram',  url: '#' },
    { name: 'YouTube',   icon: 'mdi:youtube',    url: '#' },
    { name: 'LinkedIn',  icon: 'mdi:linkedin',   url: '#' },
    { name: 'Instagram', icon: 'mdi:instagram',  url: '#' },
]

const quickLinksList = [
    { label: 'Universitet haqida', href: '/universitet/haqida/umumiy' },
    { label: 'Tuzilma',            href: '/tuzilma/rektor' },
    { label: 'Yangiliklar',        href: '/yangiliklar' },
    { label: 'Maqolalar',          href: '/maqolalar/mahalliy' },
]

const usefulLinks = [
    { label: 'Qabul 2025',       href: '/qabul/ariza' },
    { label: 'Talabalar uchun',  href: '/talabalar/bakalavr/yoriqnoma' },
    { label: 'FAQ',              href: '/qabul/eslatma' },
    { label: 'Sayt xaritasi',    href: '/' },
]

const newsletterEmail = ref('')
const subscribed = ref(false)

const subscribe = () => {
    if (!newsletterEmail.value.trim()) return
    subscribed.value = true
}
</script>

<style scoped>
.footer-link {
    transition: color 0.2s ease, transform 0.2s ease;
    display: inline-block;
}
.footer-link:hover {
    color: #fff;
    transform: translateX(3px);
}
</style>
