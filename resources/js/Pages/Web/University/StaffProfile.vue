<template>
    <WebLayout :settings="settings">

        <!-- HERO — MUHIM: TUZATILDI. Avvalgi versiyada bu sahifa uchun
             butunlay boshqa (to'q-navy, o'zi yozilgan) hero qurilgan edi —
             foydalanuvchi buni to'g'ri ravishda rad etdi: About.vue va
             Staff.vue sahifalarida ishlatilayotgan umumiy PageHero.vue
             componentiga (indigo qiya panel + fon rasm) mos kelishi kerak
             edi. Endi xuddi o'sha component ishlatilmoqda — yagona farq:
             PageHero'ga qo'shilgan ixtiyoriy "with-photo"/"photo" propi
             orqali o'qituvchining rasmi sarlavha yonida chiqariladi.
             Tadqiqot teglari, aloqa ma'lumotlari va tugmalar hero'dan olib
             tashlanib, pastdagi "Umumiy ma'lumot" kartasiga ko'chirildi. -->
        <PageHero
            :crumbs="crumbs"
            :title="staff.full_name_uz"
            :subtitle="heroSubtitle"
            image="/sliders/slide2.jpg"
            with-photo
            :photo="staff.photo"
            :badge="degreeLabel ? { icon: 'mdi:certificate-outline', value: degreeLabel, label: 'Ilmiy daraja' } : null"
        />

        <!-- TABLAR — MUHIM (TUZATILDI): ilgari bular oddiy "#anchor"
             havolalari edi — bosilganda butun sahifa o'sha bo'limgacha
             sakrab-skroll qilardi, foydalanuvchi buni "butun sahifa
             o'zgargandek" his qilgani uchun rad etdi. Endi haqiqiy tab
             almashtirish: bosilganda faqat pastdagi mos content
             (activeTab orqali) ko'rsatiladi/yashiriladi, sahifa o'zi
             joyidan qo'zg'almaydi. "Maqolalar", "Loyiha va dasturlar" va
             "Aloqa" uchun hali mazmun yo'q, shuning uchun bosilmaydi. -->
        <div class="bg-white border-b border-gray-100">
            <div class="container mx-auto px-4">
                <nav class="flex items-center gap-6 overflow-x-auto text-sm font-semibold">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        type="button"
                        :disabled="!tab.enabled"
                        @click="activeTab = tab.id"
                        class="py-4 border-b-2 whitespace-nowrap transition"
                        :class="!tab.enabled
                            ? 'border-transparent text-gray-300 cursor-not-allowed'
                            : (activeTab === tab.id
                                ? 'border-brand-600 text-brand-600'
                                : 'border-transparent text-gray-500 hover:text-navy-900 hover:border-gray-300')"
                    >
                        {{ tab.label }}
                    </button>
                </nav>
            </div>
        </div>

        <section class="py-10 md:py-12 bg-gray-50">
            <div class="container mx-auto px-4">
                <div class="grid lg:grid-cols-3 gap-8">

                    <!-- CHAP USTUN -->
                    <div class="lg:col-span-2 space-y-6">

                        <div v-show="activeTab === 'overview'" id="umumiy-malumot" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8">
                            <h2 class="flex items-center gap-2.5 text-lg font-bold text-navy-900 mb-4">
                                <Icon icon="mdi:card-account-details-outline" class="w-5 h-5 text-brand-600" />
                                Umumiy ma'lumot
                            </h2>

                            <!-- MUHIM: bular ilgari hero ichida edi — PageHero.vue
                                 boshqa sahifalar bilan bir xil (sodda) qolishi
                                 uchun bu yerga ko'chirildi. -->
                            <div v-if="staff.research_tags?.length" class="flex flex-wrap gap-2 mb-4">
                                <span
                                    v-for="tag in staff.research_tags"
                                    :key="tag"
                                    class="bg-brand-50 text-brand-700 text-xs font-medium px-3 py-1 rounded-full"
                                >
                                    {{ tag }}
                                </span>
                            </div>

                            <div class="flex flex-wrap gap-x-5 gap-y-2 text-gray-500 text-sm mb-4">
                                <span v-if="staff.phone" class="inline-flex items-center gap-1.5">
                                    <Icon icon="mdi:phone-outline" class="w-4 h-4 text-brand-500" /> {{ staff.phone }}
                                </span>
                                <span v-if="staff.email" class="inline-flex items-center gap-1.5">
                                    <Icon icon="mdi:email-outline" class="w-4 h-4 text-brand-500" /> {{ staff.email }}
                                </span>
                                <span v-if="staff.location" class="inline-flex items-center gap-1.5">
                                    <Icon icon="mdi:map-marker-outline" class="w-4 h-4 text-brand-500" /> {{ staff.location }}
                                </span>
                            </div>

                            <div class="flex flex-wrap gap-3 mb-5">
                                <a
                                    v-if="staff.email"
                                    :href="`mailto:${staff.email}`"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold transition"
                                >
                                    <Icon icon="mdi:email-outline" class="w-4 h-4" />
                                    Bog'lanish
                                </a>
                                <!-- MUHIM: staff.cv_file hozircha hech bir yozuvda
                                     to'ldirilmagan (fayl yuklash imkoniyati hali
                                     qurilmagan) — shuning uchun tugma faqat mavjud
                                     bo'lsa ko'rinadi. -->
                                <a
                                    v-if="staff.cv_file"
                                    :href="staff.cv_file"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-200 text-navy-900 text-sm font-semibold hover:bg-gray-50 transition"
                                >
                                    <Icon icon="mdi:download-outline" class="w-4 h-4" />
                                    CV yuklab olish
                                </a>
                            </div>

                            <p v-if="staff.bio_uz" class="text-gray-600 text-sm leading-relaxed">{{ staff.bio_uz }}</p>

                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
                                <div
                                    v-for="s in statCards"
                                    :key="s.label"
                                    class="bg-brand-50/60 rounded-xl p-4 text-center"
                                >
                                    <Icon :icon="s.icon" class="w-5 h-5 text-brand-600 mx-auto mb-1.5" />
                                    <p class="text-lg font-bold text-navy-900 leading-tight">{{ s.value }}</p>
                                    <p class="text-gray-500 text-[11px] mt-0.5">{{ s.label }}</p>
                                </div>
                            </div>
                        </div>

                        <div
                            v-show="activeTab === 'education'"
                            v-if="staff.educations?.length"
                            id="talim-va-malaka"
                            class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8"
                        >
                            <h2 class="flex items-center gap-2.5 text-lg font-bold text-navy-900 mb-5">
                                <Icon icon="mdi:school-outline" class="w-5 h-5 text-brand-600" />
                                Ta'lim va malaka
                            </h2>
                            <ol class="relative border-l-2 border-brand-100 pl-6 space-y-6">
                                <li v-for="edu in staff.educations" :key="edu.id" class="relative">
                                    <span class="absolute -left-[31px] top-1 w-3.5 h-3.5 rounded-full bg-brand-600 ring-4 ring-brand-50"></span>
                                    <p class="text-xs text-gray-400 font-medium mb-0.5">{{ edu.period }}</p>
                                    <p class="font-bold text-navy-900 text-sm">{{ edu.title }}</p>
                                    <p v-if="edu.subtitle" class="text-gray-500 text-xs">{{ edu.subtitle }}</p>
                                </li>
                            </ol>
                        </div>

                        <div
                            v-show="activeTab === 'experience'"
                            v-if="staff.experiences?.length"
                            id="ish-tajribasi"
                            class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8"
                        >
                            <h2 class="flex items-center gap-2.5 text-lg font-bold text-navy-900 mb-5">
                                <Icon icon="mdi:briefcase-outline" class="w-5 h-5 text-brand-600" />
                                Ish tajribasi
                            </h2>
                            <ol class="relative border-l-2 border-brand-100 pl-6 space-y-6">
                                <li v-for="exp in staff.experiences" :key="exp.id" class="relative">
                                    <span class="absolute -left-[31px] top-1 w-3.5 h-3.5 rounded-full bg-brand-600 ring-4 ring-brand-50"></span>
                                    <p class="text-xs text-gray-400 font-medium mb-0.5">{{ exp.period }}</p>
                                    <p class="font-bold text-navy-900 text-sm">{{ exp.title }}</p>
                                    <p v-if="exp.subtitle" class="text-gray-500 text-xs">{{ exp.subtitle }}</p>
                                </li>
                            </ol>
                        </div>

                        <div
                            v-show="activeTab === 'research'"
                            v-if="staff.research_summary_uz"
                            id="ilmiy-faoliyati"
                            class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8"
                        >
                            <h2 class="flex items-center gap-2.5 text-lg font-bold text-navy-900 mb-4">
                                <Icon icon="mdi:lightbulb-on-outline" class="w-5 h-5 text-brand-600" />
                                Ilmiy faoliyati
                            </h2>
                            <p class="text-gray-600 text-sm leading-relaxed mb-4">{{ staff.research_summary_uz }}</p>
                            <button
                                type="button"
                                @click="activeTab = 'articles'"
                                class="inline-flex items-center gap-1.5 text-brand-600 text-sm font-semibold hover:gap-2.5 transition-all duration-300"
                            >
                                Batafsil ko'rish
                                <Icon icon="mdi:arrow-right" class="w-4 h-4" />
                            </button>
                        </div>

                        <!-- MAQOLALAR -->
                        <div v-show="activeTab === 'articles'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8">
                            <h2 class="flex items-center gap-2.5 text-lg font-bold text-navy-900 mb-5">
                                <Icon icon="mdi:file-document-outline" class="w-5 h-5 text-brand-600" />
                                Maqolalar
                            </h2>
                            <div v-if="staff.articles?.length" class="space-y-3">
                                <div
                                    v-for="article in staff.articles"
                                    :key="article.id"
                                    class="flex items-start gap-3 p-4 rounded-xl border border-gray-100 hover:border-brand-200 transition"
                                >
                                    <span class="flex-shrink-0 w-10 h-10 rounded-lg bg-brand-50 flex items-center justify-center">
                                        <Icon icon="mdi:file-document-outline" class="w-5 h-5 text-brand-600" />
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold text-navy-900 text-sm leading-snug">{{ article.title }}</p>
                                        <p class="text-gray-500 text-xs mt-1">{{ [article.source_uz, article.year].filter(Boolean).join(' · ') }}</p>
                                    </div>
                                    <a
                                        v-if="article.url"
                                        :href="article.url"
                                        target="_blank"
                                        rel="noopener"
                                        class="flex-shrink-0 text-brand-600 hover:text-brand-700"
                                    >
                                        <Icon icon="mdi:open-in-new" class="w-4 h-4" />
                                    </a>
                                </div>
                            </div>
                            <p v-else class="text-gray-400 text-sm">Hozircha maqolalar kiritilmagan.</p>
                        </div>

                        <!-- LOYIHA VA DASTURLAR -->
                        <div v-show="activeTab === 'projects'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8">
                            <h2 class="flex items-center gap-2.5 text-lg font-bold text-navy-900 mb-5">
                                <Icon icon="mdi:cog-outline" class="w-5 h-5 text-brand-600" />
                                Loyiha va dasturlar
                            </h2>
                            <div v-if="staff.projects?.length" class="space-y-3">
                                <div
                                    v-for="project in staff.projects"
                                    :key="project.id"
                                    class="p-4 rounded-xl border border-gray-100 hover:border-brand-200 transition"
                                >
                                    <div class="flex items-start justify-between gap-3 mb-1.5">
                                        <p class="font-semibold text-navy-900 text-sm">{{ project.title }}</p>
                                        <span v-if="project.period" class="flex-shrink-0 text-[11px] font-medium text-gray-400 mt-0.5">{{ project.period }}</span>
                                    </div>
                                    <p v-if="project.description_uz" class="text-gray-500 text-xs leading-relaxed mb-2.5">{{ project.description_uz }}</p>
                                    <div class="flex items-center flex-wrap gap-2.5">
                                        <span v-if="project.role_uz" class="inline-flex items-center text-[11px] font-medium text-brand-700 bg-brand-50 px-2.5 py-1 rounded-full">
                                            {{ project.role_uz }}
                                        </span>
                                        <a
                                            v-if="project.url"
                                            :href="project.url"
                                            target="_blank"
                                            rel="noopener"
                                            class="text-brand-600 text-xs font-semibold inline-flex items-center gap-1"
                                        >
                                            Havola
                                            <Icon icon="mdi:open-in-new" class="w-3.5 h-3.5" />
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <p v-else class="text-gray-400 text-sm">Hozircha loyihalar kiritilmagan.</p>
                        </div>

                        <!-- ALOQA -->
                        <div v-show="activeTab === 'contact'" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 md:p-8">
                            <h2 class="flex items-center gap-2.5 text-lg font-bold text-navy-900 mb-5">
                                <Icon icon="mdi:card-account-phone-outline" class="w-5 h-5 text-brand-600" />
                                Aloqa
                            </h2>
                            <div class="grid sm:grid-cols-2 gap-5 mb-5">
                                <div v-if="staff.phone" class="flex items-start gap-3">
                                    <Icon icon="mdi:phone-outline" class="w-4.5 h-4.5 text-brand-500 mt-0.5 flex-shrink-0" />
                                    <div class="min-w-0">
                                        <p class="text-gray-400 text-[11px]">Telefon</p>
                                        <p class="text-navy-900 text-sm font-semibold">{{ staff.phone }}</p>
                                    </div>
                                </div>
                                <div v-if="staff.email" class="flex items-start gap-3">
                                    <Icon icon="mdi:email-outline" class="w-4.5 h-4.5 text-brand-500 mt-0.5 flex-shrink-0" />
                                    <div class="min-w-0">
                                        <p class="text-gray-400 text-[11px]">Email</p>
                                        <p class="text-navy-900 text-sm font-semibold">{{ staff.email }}</p>
                                    </div>
                                </div>
                                <div v-if="staff.location" class="flex items-start gap-3">
                                    <Icon icon="mdi:map-marker-outline" class="w-4.5 h-4.5 text-brand-500 mt-0.5 flex-shrink-0" />
                                    <div class="min-w-0">
                                        <p class="text-gray-400 text-[11px]">Manzil</p>
                                        <p class="text-navy-900 text-sm font-semibold">{{ staff.location }}</p>
                                    </div>
                                </div>
                                <div v-if="staff.reception_hours" class="flex items-start gap-3">
                                    <Icon icon="mdi:clock-outline" class="w-4.5 h-4.5 text-brand-500 mt-0.5 flex-shrink-0" />
                                    <div class="min-w-0">
                                        <p class="text-gray-400 text-[11px]">Qabul kunlari</p>
                                        <p class="text-navy-900 text-sm font-semibold">{{ staff.reception_hours }}</p>
                                    </div>
                                </div>
                                <div v-if="staff.department" class="flex items-start gap-3">
                                    <Icon icon="mdi:office-building-outline" class="w-4.5 h-4.5 text-brand-500 mt-0.5 flex-shrink-0" />
                                    <div class="min-w-0">
                                        <p class="text-gray-400 text-[11px]">Kafedra</p>
                                        <p class="text-navy-900 text-sm font-semibold">{{ staff.department.name_uz }}</p>
                                    </div>
                                </div>
                            </div>
                            <a
                                v-if="staff.email"
                                :href="`mailto:${staff.email}`"
                                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold transition"
                            >
                                <Icon icon="mdi:email-outline" class="w-4 h-4" />
                                Bog'lanish
                            </a>
                        </div>

                        <!-- MUHIM: "Talabalar uchun" kartasi maxsus staff
                             ma'lumotiga bog'liq emas — har bir profilda bir xil,
                             manzil sahifasi hali qurilmagani uchun "#"ga
                             ishora qiladi. -->
                        <div class="bg-brand-50 rounded-2xl p-5 flex items-center justify-between gap-4 flex-wrap">
                            <div class="flex items-center gap-3">
                                <span class="flex-shrink-0 w-11 h-11 rounded-xl bg-white flex items-center justify-center">
                                    <Icon icon="mdi:folder-outline" class="w-5 h-5 text-brand-600" />
                                </span>
                                <div>
                                    <p class="font-bold text-navy-900 text-sm">Talabalar uchun</p>
                                    <p class="text-gray-500 text-xs">Dars jadvali, ma'ruza materiallari va boshqa fayllar</p>
                                </div>
                            </div>
                            <a href="#" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-brand-200 text-brand-700 text-xs font-semibold hover:bg-white transition flex-shrink-0">
                                Ko'rish
                                <Icon icon="mdi:arrow-right" class="w-3.5 h-3.5" />
                            </a>
                        </div>
                    </div>

                    <!-- O'NG SIDEBAR -->
                    <div class="space-y-6">

                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                            <h3 class="flex items-center gap-2 text-sm font-bold text-navy-900 mb-4">
                                <Icon icon="mdi:information-outline" class="w-4.5 h-4.5 text-brand-600" />
                                Qisqa ma'lumot
                            </h3>
                            <ul class="space-y-4">
                                <li v-if="staff.faculty" class="flex items-start gap-3">
                                    <Icon icon="mdi:bank-outline" class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                    <div class="min-w-0">
                                        <p class="text-gray-400 text-[11px]">Fakultet</p>
                                        <p class="text-navy-900 text-sm font-semibold truncate">{{ staff.faculty.name_uz }}</p>
                                    </div>
                                </li>
                                <li v-if="staff.department" class="flex items-start gap-3">
                                    <Icon icon="mdi:office-building-outline" class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                    <div class="min-w-0">
                                        <p class="text-gray-400 text-[11px]">Kafedra</p>
                                        <p class="text-navy-900 text-sm font-semibold truncate">{{ staff.department.name_uz }}</p>
                                    </div>
                                </li>
                                <li v-if="staff.position_uz" class="flex items-start gap-3">
                                    <Icon icon="mdi:account-tie-outline" class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                    <div class="min-w-0">
                                        <p class="text-gray-400 text-[11px]">Lavozim</p>
                                        <p class="text-navy-900 text-sm font-semibold truncate">{{ staff.position_uz }}</p>
                                    </div>
                                </li>
                                <li v-if="degreeLabel" class="flex items-start gap-3">
                                    <Icon icon="mdi:certificate-outline" class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                    <div class="min-w-0">
                                        <p class="text-gray-400 text-[11px]">Ilmiy daraja</p>
                                        <p class="text-navy-900 text-sm font-semibold truncate">{{ degreeLabel }}</p>
                                    </div>
                                </li>
                                <li v-if="staff.phone" class="flex items-start gap-3">
                                    <Icon icon="mdi:phone-outline" class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                    <div class="min-w-0">
                                        <p class="text-gray-400 text-[11px]">Telefon</p>
                                        <p class="text-navy-900 text-sm font-semibold truncate">{{ staff.phone }}</p>
                                    </div>
                                </li>
                                <li v-if="staff.email" class="flex items-start gap-3">
                                    <Icon icon="mdi:email-outline" class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                    <div class="min-w-0">
                                        <p class="text-gray-400 text-[11px]">Email</p>
                                        <p class="text-navy-900 text-sm font-semibold truncate">{{ staff.email }}</p>
                                    </div>
                                </li>
                            </ul>
                        </div>

                        <div v-if="staff.social_links?.length" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                            <h3 class="flex items-center gap-2 text-sm font-bold text-navy-900 mb-4">
                                <Icon icon="mdi:share-variant-outline" class="w-4.5 h-4.5 text-brand-600" />
                                Ijtimoiy tarmoqlar
                            </h3>
                            <ul class="space-y-3.5">
                                <li v-for="link in staff.social_links" :key="link.id">
                                    <a :href="link.url" target="_blank" rel="noopener" class="flex items-center gap-3 group">
                                        <span class="flex-shrink-0 w-9 h-9 rounded-lg bg-brand-50 flex items-center justify-center text-brand-600 group-hover:bg-brand-600 group-hover:text-white transition">
                                            <Icon :icon="socialIcon(link.platform)" class="w-4.5 h-4.5" />
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-gray-400 text-[11px]">{{ socialName(link.platform) }}</p>
                                            <p class="text-navy-900 text-sm font-semibold truncate group-hover:text-brand-600 transition">{{ link.label }}</p>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </div>

                        <div v-if="colleagues.length" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="flex items-center gap-2 text-sm font-bold text-navy-900">
                                    <Icon icon="mdi:account-group-outline" class="w-4.5 h-4.5 text-brand-600" />
                                    Hamkasblar
                                </h3>
                                <Link :href="route('university.about.staff', { department_id: staff.department_id })" class="text-brand-600 text-xs font-semibold hover:underline">
                                    Barchasi →
                                </Link>
                            </div>
                            <ul class="space-y-3.5">
                                <li v-for="c in colleagues" :key="c.id">
                                    <Link :href="route('university.about.staff.show', c.id)" class="flex items-center gap-3 group">
                                        <span class="flex-shrink-0 w-10 h-10 rounded-full bg-brand-50 overflow-hidden flex items-center justify-center">
                                            <img v-if="c.photo" :src="c.photo" :alt="c.full_name_uz" class="w-full h-full object-cover">
                                            <Icon v-else icon="mdi:account" class="w-5 h-5 text-brand-300" />
                                        </span>
                                        <div class="min-w-0">
                                            <p class="text-navy-900 text-sm font-semibold truncate group-hover:text-brand-600 transition">{{ c.full_name_uz }}</p>
                                            <p class="text-gray-400 text-xs truncate">{{ c.position_uz }}</p>
                                        </div>
                                    </Link>
                                </li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
        </section>

    </WebLayout>
</template>

<script setup>
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import WebLayout from '@/Layouts/WebLayout.vue'
import PageHero from '@/Components/Web/PageHero.vue'

const props = defineProps({
    staff:      { type: Object, required: true },
    colleagues: { type: Array, default: () => [] },
    settings:   { type: Object, default: () => ({}) },
})

// About.vue va Staff.vue'dagi bilan bir xil PageHero component — faqat
// bu sahifada with-photo orqali o'qituvchi surati qo'shiladi.
const crumbs = computed(() => [
    { label: 'Asosiy', href: '/' },
    { label: "Professor & o'qituvchilar", href: route('university.about.staff') },
    { label: props.staff.full_name_uz },
])

const heroSubtitle = computed(() => {
    return [props.staff.faculty?.name_uz, props.staff.department?.name_uz]
        .filter(Boolean)
        .join(' — ')
})

// MUHIM: "Maqolalar", "Loyiha va dasturlar" va "Aloqa" uchun hali alohida
// mazmun qurilmagan (foydalanuvchi bilan kelishilganidek, bu safar faqat
// "Umumiy ma'lumot" to'liq qilinmoqda) — shuning uchun enabled=false va
// bosilmaydigan holatda. Boshlang'ich faol tab — "Umumiy ma'lumot", chunki
// hozircha faqat shu tab to'liq ishlab chiqilgan.
const activeTab = ref('overview')

const tabs = [
    { id: 'overview',   label: "Umumiy ma'lumot",     enabled: true },
    { id: 'education',  label: "Ta'lim va malaka",    enabled: true },
    { id: 'experience', label: 'Ish tajribasi',       enabled: true },
    { id: 'research',   label: 'Ilmiy faoliyati',     enabled: true },
    { id: 'articles',   label: 'Maqolalar',           enabled: true },
    { id: 'projects',   label: 'Loyiha va dasturlar', enabled: true },
    { id: 'contact',    label: 'Aloqa',               enabled: true },
]

const degreeOptions = [
    { value: 'professor', label: 'Professor' },
    { value: 'dotsent',   label: 'Dotsent' },
    { value: 'phd',       label: 'PhD' },
    { value: 'oqituvchi', label: "O'qituvchi" },
]
const degreeLabel = computed(() => degreeOptions.find(o => o.value === props.staff.degree)?.label ?? '')

const statCards = computed(() => [
    { icon: 'mdi:calendar-check-outline',  value: `${props.staff.experience_years ?? 0}+`, label: 'Yillik tajriba' },
    { icon: 'mdi:account-group-outline',   value: `${props.staff.students_count ?? 0}+`,   label: 'Talabalar' },
    { icon: 'mdi:lightbulb-on-outline',    value: `${props.staff.articles_count ?? 0}+`,   label: 'Ilmiy maqola' },
    { icon: 'mdi:cog-outline',             value: `${props.staff.projects_count ?? 0}+`,   label: 'Loyiha' },
])

// Ijtimoiy tarmoqlar kartasidagi har bir platformaga mos icon/nom.
const socialMeta = {
    telegram:       { icon: 'mdi:telegram',        name: 'Telegram' },
    linkedin:       { icon: 'mdi:linkedin',        name: 'LinkedIn' },
    google_scholar: { icon: 'mdi:school-outline',  name: 'Google Scholar' },
    researchgate:   { icon: 'mdi:book-outline',    name: 'ResearchGate' },
}
const socialIcon = (platform) => socialMeta[platform]?.icon ?? 'mdi:link-variant'
const socialName = (platform) => socialMeta[platform]?.name ?? platform
</script>
