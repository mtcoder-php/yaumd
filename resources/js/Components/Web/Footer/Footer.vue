<template>
    <!-- Footer top — 4 ustunli -->
    <section class="py-16 px-4 bg-white border-t border-gray-200">
        <div class="container mx-auto">

            <!-- Desktop -->
            <div class="hidden lg:flex items-start justify-between gap-8">
                <div
                    v-for="section in footerSections"
                    :key="section.id"
                    class="w-1/4 group"
                >
                    <h3 class="text-lg font-bold text-gray-800 mb-4 group-hover:text-[#0f3460] transition-colors duration-300">
                        {{ section.title }}
                    </h3>

                    <p v-if="section.description" class="text-gray-500 text-sm mb-4 leading-relaxed">
                        {{ section.description }}
                    </p>

                    <ul v-if="section.links" class="space-y-2">
                        <li v-for="(link, i) in section.links" :key="i">
                            <Link :href="link.href || '#'" class="footer-link text-gray-500 text-sm flex items-center gap-1">
                                <Icon icon="mdi:chevron-right" class="w-3.5 h-3.5 text-gray-300" />
                                {{ link.label || link }}
                            </Link>
                        </li>
                    </ul>

                    <div v-if="section.isContact" class="space-y-3">
                        <div v-for="(info, i) in section.contactInfo" :key="i" class="contact-item">
                            <div v-if="info.type === 'address'">
                                <p v-for="(line, j) in info.content" :key="j" class="text-gray-500 text-sm">
                                    {{ line }}
                                </p>
                            </div>
                            <a v-else :href="info.href" class="footer-link text-gray-500 text-sm">
                                {{ info.content }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mobile — accordion -->
            <div class="lg:hidden space-y-2">
                <div v-for="section in footerSections" :key="section.id" class="border-b border-gray-100 pb-2">
                    <button
                        @click="toggle(section.id)"
                        class="w-full flex items-center justify-between py-3"
                    >
                        <h3 class="text-base font-bold text-gray-800">{{ section.title }}</h3>
                        <Icon
                            :icon="openSection === section.id ? 'mdi:chevron-up' : 'mdi:chevron-down'"
                            class="w-5 h-5 text-gray-400"
                        />
                    </button>

                    <div
                        class="overflow-hidden transition-all duration-300"
                        :class="openSection === section.id ? 'max-h-96 opacity-100 pb-3' : 'max-h-0 opacity-0'"
                    >
                        <p v-if="section.description" class="text-gray-500 text-sm mb-3 leading-relaxed">
                            {{ section.description }}
                        </p>
                        <ul v-if="section.links" class="space-y-2">
                            <li v-for="(link, i) in section.links" :key="i">
                                <Link :href="link.href || '#'" class="footer-link text-gray-500 text-sm flex items-center gap-1">
                                    <Icon icon="mdi:chevron-right" class="w-3.5 h-3.5 text-gray-300" />
                                    {{ link.label || link }}
                                </Link>
                            </li>
                        </ul>
                        <div v-if="section.isContact" class="space-y-3">
                            <div v-for="(info, i) in section.contactInfo" :key="i">
                                <div v-if="info.type === 'address'">
                                    <p v-for="(line, j) in info.content" :key="j" class="text-gray-500 text-sm">{{ line }}</p>
                                </div>
                                <a v-else :href="info.href" class="footer-link text-gray-500 text-sm">{{ info.content }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Footer bottom -->
    <footer class="py-4 bg-white border-t border-gray-200">
        <div class="container mx-auto px-4">

            <!-- Desktop -->
            <div class="hidden md:flex items-center justify-between">
                <!-- Ijtimoiy -->
                <div class="flex items-center gap-2">
                    <a
                        v-for="s in socials"
                        :key="s.name"
                        :href="s.url"
                        target="_blank"
                        :title="s.name"
                        class="social-btn w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center transition-all duration-300 hover:bg-[#0f3460] hover:border-[#0f3460] group"
                    >
                        <Icon :icon="s.icon" class="w-5 h-5 text-gray-500 group-hover:text-white transition-colors" />
                    </a>
                </div>

                <!-- Copyright -->
                <p class="text-sm text-gray-400">
                    © {{ year }} — Yangi Asr Universiteti.
                    <a href="#" class="hover:text-[#0f3460] transition-colors">MTCoder™</a>
                </p>

                <!-- Bo'sh -->
                <div class="w-40"></div>
            </div>

            <!-- Mobile -->
            <div class="md:hidden flex flex-col items-center gap-3">
                <div class="flex items-center gap-2">
                    <a
                        v-for="s in socials"
                        :key="s.name"
                        :href="s.url"
                        target="_blank"
                        class="social-btn w-9 h-9 rounded-full border border-gray-200 flex items-center justify-center hover:bg-[#0f3460] hover:border-[#0f3460] group transition-all duration-300"
                    >
                        <Icon :icon="s.icon" class="w-4 h-4 text-gray-500 group-hover:text-white transition-colors" />
                    </a>
                </div>
                <p class="text-xs text-gray-400 text-center">
                    © {{ year }} — Yangi Asr Universiteti. MTCoder™
                </p>
            </div>

        </div>
    </footer>
</template>

<script setup>
import { ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'

const year = new Date().getFullYear()
const openSection = ref(null)

const toggle = (id) => {
    openSection.value = openSection.value === id ? null : id
}

const socials = [
    { name: 'YouTube',   icon: 'mdi:youtube',    url: '#' },
    { name: 'Telegram',  icon: 'mdi:telegram',   url: '#' },
    { name: 'Instagram', icon: 'mdi:instagram',  url: '#' },
    { name: 'Facebook',  icon: 'mdi:facebook',   url: '#' },
]

const footerSections = [
    {
        id: 'about',
        title: 'Universitet haqida',
        description: "Yangi Asr universiteti — zamonaviy ta'lim va ilmiy tadqiqotlar markazi sifatida faoliyat yurituvchi yetakchi oliy ta'lim muassasasi.",
        links: [
            { label: "Umumiy ma'lumot",  href: '/universitet/haqida/umumiy' },
            { label: 'Tariximiz',         href: '/universitet/haqida/tarix' },
            { label: 'Yutuqlarimiz',      href: '/universitet/haqida/yutuqlar' },
            { label: 'Rahbariyat',        href: '/tuzilma/rektor' },
        ]
    },
    {
        id: 'programs',
        title: "Ta'lim dasturlari",
        links: [
            { label: 'Dasturiy injiniring', href: '/yonalishlar/dasturiy-injiniring' },
            { label: 'Matematika',           href: '/yonalishlar/matematika' },
            { label: 'Psixologiya',          href: '/yonalishlar/psixologiya' },
            { label: 'Arab tili',            href: '/yonalishlar/arab-tili' },
            { label: 'Iqtisodiyot',          href: '/yonalishlar/iqtisodiyot' },
            { label: 'Ingliz tili',          href: '/yonalishlar/ingliz-tili' },
        ]
    },
    {
        id: 'useful',
        title: 'Foydali havolalar',
        links: [
            { label: 'Abituriyentlar uchun', href: '/qabul' },
            { label: 'Elektron kutubxona',   href: '/kutubxona' },
            { label: 'Yangiliklar',          href: '/yangiliklar' },
            { label: 'Tadbirlar',            href: '/elonlar' },
            { label: 'Hujjatlar',            href: '/hujjatlar' },
        ]
    },
    {
        id: 'contact',
        title: "Bog'lanish",
        isContact: true,
        contactInfo: [
            {
                type: 'address',
                content: ["Toshkent shahri, Chilonzor tumani", "So'galli ota ko'chasi, 5-uy"]
            },
            { type: 'phone',   content: '+998 (71) 207-77-55', href: 'tel:+998712077755' },
            { type: 'email',   content: 'info@yangiasr.uz',    href: 'mailto:info@yangiasr.uz' },
            { type: 'website', content: 'www.yangiasr.uz',     href: '/' },
        ]
    },
]
</script>

<style scoped>
.footer-link {
    transition: all 0.3s ease;
}
.footer-link:hover {
    color: #0f3460;
    transform: translateX(5px);
}

.contact-item {
    transition: all 0.3s ease;
    border-radius: 8px;
}
.contact-item:hover {
    background-color: #f8fafc;
    padding: 6px 8px;
    margin: -6px -8px;
}
</style>
