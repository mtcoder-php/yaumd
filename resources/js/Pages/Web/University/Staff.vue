<template>
    <WebLayout :settings="settings">
        <PageHero
            :crumbs="crumbs"
            title="Professor & o'qituvchilar"
            subtitle="Yangi Asr Universitetida o'z sohasining yetuk mutaxassislari va tajribali professor-o'qituvchilar faoliyat yuritadi."
            image="/sliders/slide2.png"
        />

        <section class="py-10 md:py-12 bg-gray-50">
            <div class="container mx-auto px-4 space-y-8">

                <!-- Statistika — filtrga qaramay har doim umumiy sonlar -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div
                        v-for="s in statCards"
                        :key="s.label"
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3.5"
                    >
                        <span class="flex-shrink-0 w-12 h-12 rounded-full bg-brand-50 flex items-center justify-center">
                            <Icon :icon="s.icon" class="w-6 h-6 text-brand-600" />
                        </span>
                        <div>
                            <p class="text-xl font-bold text-navy-900 leading-tight">{{ s.value }}</p>
                            <p class="text-gray-500 text-xs mt-0.5">{{ s.label }}</p>
                        </div>
                    </div>
                </div>

                <!-- Qidiruv va filtrlar -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4">
                    <div class="flex flex-col lg:flex-row gap-3">
                        <div class="relative flex-1 min-w-0">
                            <Icon icon="mdi:magnify" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4.5 h-4.5 text-gray-400" />
                            <input
                                v-model="form.search"
                                type="text"
                                placeholder="Ism, familiya yoki kafedra bo'yicha qidirish..."
                                class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 text-sm text-navy-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-brand-500/30 focus:border-brand-400"
                                @keyup.enter="search"
                            >
                        </div>

                        <select
                            v-model="form.faculty_id"
                            class="px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm text-navy-900 bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                            @change="form.department_id = ''"
                        >
                            <option value="">Fakultet: Barchasi</option>
                            <option v-for="f in faculties" :key="f.id" :value="f.id">{{ f.short_name || f.name_uz }}</option>
                        </select>

                        <select
                            v-model="form.department_id"
                            class="px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm text-navy-900 bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                        >
                            <option value="">Kafedra: Barchasi</option>
                            <option v-for="d in filteredDepartments" :key="d.id" :value="d.id">{{ d.name_uz }}</option>
                        </select>

                        <select
                            v-model="form.degree"
                            class="px-3.5 py-2.5 rounded-xl border border-gray-200 text-sm text-navy-900 bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/30"
                        >
                            <option value="">Ilmiy daraja: Barchasi</option>
                            <option v-for="opt in degreeOptions" :key="opt.value" :value="opt.value">{{ opt.label }}</option>
                        </select>

                        <button
                            type="button"
                            @click="search"
                            class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold transition flex-shrink-0"
                        >
                            <Icon icon="mdi:magnify" class="w-4 h-4" />
                            Qidirish
                        </button>
                    </div>
                </div>

                <!-- Ro'yxat sarlavhasi + tartiblash — MUHIM: 2 marta (avval
                     bg-white/border bilan, keyin appearance-none bilan)
                     tuzatishga urinildi, lekin foydalanuvchining brauzerida
                     select'ning o'zi chizadigan matn baribir yopiq holatda
                     ko'rinmasdi (native select rendering nosozligi). Shuning
                     uchun endi select'ning matniga umuman tayanilmaydi —
                     ko'rinadigan matn oddiy <span> orqali chiqariladi
                     (sortLabel), select esa unga qatlam sifatida ustidan
                     butunlay shaffof (opacity-0) holda qo'yiladi va faqat
                     bosilganda native dropdown ochish uchun ishlatiladi. Bu
                     usul brauzerdan qat'i nazar 100% ishlaydi. -->
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <h2 class="text-lg font-bold text-navy-900">Professor-o'qituvchilar</h2>
                    <div class="relative inline-flex items-center gap-1 text-sm text-gray-500 cursor-pointer">
                        <span>{{ sortLabel }}</span>
                        <Icon icon="mdi:chevron-down" class="w-4 h-4 text-gray-400" />
                        <select
                            v-model="form.sort"
                            @change="search"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                            aria-label="Tartiblash"
                        >
                            <option value="name_asc">Tartiblash: A-Z</option>
                            <option value="name_desc">Tartiblash: Z-A</option>
                        </select>
                    </div>
                </div>

                <!-- Kartochkalar -->
                <div v-if="staff.data.length" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    <div
                        v-for="person in staff.data"
                        :key="person.id"
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
                    >
                        <div class="relative aspect-[4/3] bg-brand-50">
                            <img
                                v-if="person.photo"
                                :src="person.photo"
                                :alt="person.full_name_uz"
                                class="w-full h-full object-cover"
                            >
                            <div v-else class="w-full h-full flex items-center justify-center">
                                <Icon icon="mdi:account" class="w-16 h-16 text-brand-200" />
                            </div>
                            <span
                                v-if="degreeLabel(person.degree)"
                                class="absolute top-2.5 right-2.5 bg-white/95 backdrop-blur-sm text-brand-700 text-[11px] font-bold px-2.5 py-1 rounded-full shadow-sm"
                            >
                                {{ degreeLabel(person.degree) }}
                            </span>
                        </div>
                        <div class="p-4">
                            <h3 class="font-bold text-navy-900 text-sm mb-1">{{ person.full_name_uz }}</h3>
                            <p class="text-gray-500 text-xs">{{ person.faculty?.name_uz || person.faculty?.short_name }}</p>
                            <p class="text-gray-400 text-xs mb-3">{{ person.department?.name_uz }}</p>

                            <template v-if="person.research_tags?.length">
                                <p class="text-gray-400 text-[11px] font-medium mb-1.5">Tadqiqot yo'nalishlari:</p>
                                <div class="flex flex-wrap gap-1.5 mb-4">
                                    <span
                                        v-for="tag in person.research_tags"
                                        :key="tag"
                                        class="bg-brand-50 text-brand-700 text-[10px] font-medium px-2 py-1 rounded-md"
                                    >
                                        {{ tag }}
                                    </span>
                                </div>
                            </template>

                            <Link
                                :href="route('university.about.staff.show', person.id)"
                                class="inline-flex items-center gap-1.5 text-brand-600 text-xs font-semibold hover:gap-2.5 transition-all duration-300"
                            >
                                Profilni ko'rish
                                <Icon icon="mdi:arrow-right" class="w-3.5 h-3.5" />
                            </Link>
                        </div>
                    </div>
                </div>
                <div v-else class="bg-white rounded-2xl border border-gray-100 p-10 text-center">
                    <Icon icon="mdi:account-search-outline" class="w-10 h-10 text-gray-300 mx-auto mb-3" />
                    <p class="text-gray-500 text-sm">So'rovga mos professor-o'qituvchi topilmadi.</p>
                </div>

                <!-- Sahifalash — to'liq doiraviy tugmalar, faol sahifa
                     to'liq rangli, oldingi/keyingi uchun alohida doiraviy
                     chevron tugmalar (namunaga mos). -->
                <div v-if="staff.last_page > 1" class="flex items-center justify-center gap-2 flex-wrap">
                    <component
                        :is="staff.prev_page_url ? Link : 'span'"
                        :href="staff.prev_page_url ?? undefined"
                        preserve-scroll
                        class="w-10 h-10 rounded-full border flex items-center justify-center transition flex-shrink-0"
                        :class="staff.prev_page_url
                            ? 'border-gray-200 text-gray-500 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-600'
                            : 'border-gray-100 text-gray-300 cursor-not-allowed'"
                    >
                        <Icon icon="mdi:chevron-left" class="w-5 h-5" />
                    </component>

                    <template v-for="link in numberedLinks" :key="link.label">
                        <component
                            :is="link.url ? Link : 'span'"
                            :href="link.url ?? undefined"
                            preserve-scroll
                            class="w-10 h-10 rounded-full text-sm font-semibold flex items-center justify-center transition border flex-shrink-0"
                            :class="link.active
                                ? 'bg-brand-600 border-brand-600 text-white'
                                : 'border-gray-200 text-gray-500 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-600'"
                            v-html="link.label"
                        />
                    </template>

                    <component
                        :is="staff.next_page_url ? Link : 'span'"
                        :href="staff.next_page_url ?? undefined"
                        preserve-scroll
                        class="w-10 h-10 rounded-full border flex items-center justify-center transition flex-shrink-0"
                        :class="staff.next_page_url
                            ? 'border-gray-200 text-gray-500 hover:bg-brand-50 hover:border-brand-200 hover:text-brand-600'
                            : 'border-gray-100 text-gray-300 cursor-not-allowed'"
                    >
                        <Icon icon="mdi:chevron-right" class="w-5 h-5" />
                    </component>
                </div>

                <!-- Rahbariyat — MUHIM: vaqtincha ma'lumotlar bazasidan (Staff
                     modeli) EMAS, to'g'ridan-to'g'ri public/professors/
                     papkasidagi suratlar bilan shu componentda qattiq
                     yozilgan (leadershipTeam). Backend/DB orqali ulash
                     keyinroq, F.I.Sh.lar tayyor bo'lgach amalga oshiriladi.
                     MUHIM: rasm hajmi bo'yicha 2 marta xato qilindi — avval
                     aspect-square (butun karta kengligicha), keyin
                     aspect-[4/3] (hali ham baland) qilingan edi, ikkalasi
                     ham foydalanuvchi yuborgan aniq namunaga (kichik kvadrat
                     surat, matn o'ng tomonda, burchakda icon) mos kelmadi.
                     Endi namunadagi bilan AYNAN bir xil — gorizontal
                     kartochka: kichik (80px) kvadrat surat chapda, o'ng
                     tomonda lavozim (kichik) + F.I.Sh. (qalin) + lavozim
                     (kichik, pastda — namunada ham ikkalasi bor), va
                     kartaning pastki o'ng burchagida "batafsil ko'rish"
                     doiraviy icon. -->
                <div v-if="leadershipTeam.length" class="bg-brand-100 rounded-2xl p-6 md:p-8">
                    <div class="flex flex-col lg:flex-row gap-6 lg:items-start">
                        <div class="lg:w-64 flex-shrink-0">
                            <h2 class="text-xl font-bold text-navy-900 mb-2">Rahbariyat</h2>
                            <p class="text-gray-500 text-sm mb-4">
                                Universitetning ilmiy-ta'lim faoliyatini boshqaruvchi boshqaruv jamoasi.
                            </p>
                            <Link href="#" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-brand-600 hover:bg-brand-700 text-white text-xs font-semibold transition">
                                Barchasini ko'rish
                                <Icon icon="mdi:arrow-right" class="w-3.5 h-3.5" />
                            </Link>
                        </div>
                        <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div
                                v-for="lead in leadershipTeam"
                                :key="lead.photo"
                                class="relative bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3.5 transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
                            >
                                <span class="flex-shrink-0 w-20 h-20 rounded-xl bg-brand-50 overflow-hidden flex items-center justify-center">
                                    <img :src="lead.photo" :alt="lead.position" class="w-full h-full object-cover">
                                </span>
                                <div class="min-w-0 flex-1 pr-8">
                                    <p class="font-bold text-navy-900 text-sm truncate">{{ lead.name }}</p>
                                    <p class="text-gray-500 text-xs truncate">{{ lead.position }}</p>
                                </div>
                                <!-- MUHIM: professorning alohida profil sahifasi hali
                                     qurilmagan, shuning uchun tugma hozircha "#"ga
                                     ishora qiladi. -->
                                <a
                                    href="#"
                                    title="Batafsil ko'rish"
                                    class="absolute right-3 bottom-3 w-8 h-8 rounded-full bg-brand-50 flex items-center justify-center text-brand-600 hover:bg-brand-600 hover:text-white transition flex-shrink-0"
                                >
                                    <Icon icon="mdi:arrow-top-right" class="w-4 h-4" />
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Kafedralar — MUHIM: avval bu bo'lim (yolg'on) fakultetlar
                     ro'yxatini "X kafedra" bilan ko'rsatardi. Foydalanuvchi
                     namuna sifatida yuborgan rasm haqiqiy kafedralarni (har
                     birida "X yo'nalish" bilan) ko'rsatishni so'radi —
                     haqiqatda bazada FacultyDirectionSeeder orqali aynan 6
                     ta real kafedra bor, shuning uchun endi shu kafedralar
                     (departments prop, directions_count bilan) chiqariladi. -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-bold text-navy-900">Kafedralar</h2>
                        <Link href="#" class="text-brand-600 text-xs font-semibold hover:underline">Barchasi →</Link>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
                        <div
                            v-for="d in departments"
                            :key="d.id"
                            class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 flex items-center gap-3 transition-all duration-300 hover:-translate-y-1 hover:shadow-md"
                        >
                            <span class="flex-shrink-0 w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center">
                                <Icon :icon="departmentIcon(d)" class="w-5 h-5 text-brand-600" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-navy-900 font-semibold text-xs leading-tight mb-1 line-clamp-2">{{ d.name_uz }}</p>
                                <p class="text-brand-600 text-[11px] font-medium">{{ d.directions_count ?? 0 }} yo'nalish</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>
    </WebLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import WebLayout from '@/Layouts/WebLayout.vue'
import PageHero from '@/Components/Web/PageHero.vue'

const props = defineProps({
    staff:       { type: Object, required: true },
    filters:     { type: Object, default: () => ({}) },
    stats:       { type: Object, default: () => ({}) },
    leadership:  { type: Array, default: () => [] },
    faculties:   { type: Array, default: () => [] },
    departments: { type: Array, default: () => [] },
    settings:    { type: Object, default: () => ({}) },
})

const form = reactive({
    search:        props.filters.search ?? '',
    faculty_id:    props.filters.faculty_id ?? '',
    department_id: props.filters.department_id ?? '',
    degree:        props.filters.degree ?? '',
    sort:          props.filters.sort ?? 'name_asc',
})

const crumbs = [
    { label: 'Asosiy', href: '/' },
    { label: 'Universitet' },
    { label: "Professor & o'qituvchilar" },
]

// MUHIM: vaqtincha bazadan emas — to'g'ridan-to'g'ri public/professors/
// papkasidagi haqiqiy suratlar. F.I.Sh.lar hali berilmagan, shuning uchun
// placeholder. Real ism va (xohlasa) bazaga o'tkazish keyingi qadam.
const leadershipTeam = [
    { photo: '/professors/rektor.jpg',     position: 'Rektor',                                     name: "F.I.Sh. kiritiladi" },
    { photo: '/professors/prorector1.jpg', position: "O'quv ishlari bo'yicha prorektor",           name: "F.I.Sh. kiritiladi" },
    { photo: '/professors/prorektor2.jpg', position: "Yoshlar masalalari bo'yicha prorektor",      name: "F.I.Sh. kiritiladi" },
    { photo: '/professors/prorector3.jpg', position: "Ilmiy ishlar bo'yicha prorektor",            name: "F.I.Sh. kiritiladi" },
    { photo: '/professors/prorector4.jpg', position: "Moliya-iqtisod ishlari bo'yicha prorektor",  name: "F.I.Sh. kiritiladi" },
]

const degreeOptions = [
    { value: 'professor', label: 'Professor' },
    { value: 'dotsent',   label: 'Dotsent' },
    { value: 'phd',       label: 'PhD' },
    { value: 'oqituvchi', label: "O'qituvchi" },
]

const degreeLabel = (value) => degreeOptions.find(o => o.value === value)?.label ?? ''

// Tartiblash tugmasining ko'rinadigan matni — select'ning o'zi emas
// (yuqoridagi izohga qarang, native rendering nosozligi tufayli).
const sortLabel = computed(() => form.sort === 'name_desc' ? 'Tartiblash: Z-A' : 'Tartiblash: A-Z')

// Kafedralar bo'limidagi har bir kartochka uchun mavzuga mos icon —
// FacultyDirectionSeeder'dagi haqiqiy 6 ta kafedraning short_name'iga
// ko'ra. Yangi kafedra qo'shilsa (short_name mosligi topilmasa),
// standart "mdi:bank-outline" ishlatiladi.
const departmentIcons = {
    MPK:  'mdi:hand-heart-outline',
    UFK:  'mdi:book-open-page-variant-outline',
    TK:   'mdi:translate',
    MMTK: 'mdi:baby-face-outline',
    MSFK: 'mdi:book-outline',
    SFK:  'mdi:book-open-variant',
}
const departmentIcon = (d) => departmentIcons[d.short_name] ?? 'mdi:bank-outline'

// Fakultet tanlanganda faqat o'sha fakultetga tegishli kafedralar ko'rinadi.
const filteredDepartments = computed(() => {
    if (!form.faculty_id) return props.departments
    return props.departments.filter(d => String(d.faculty_id) === String(form.faculty_id))
})

// Laravel'ning staff.links massivi boshi/oxirida "&laquo; Previous" /
// "Next &raquo;" havolalarini o'z ichiga oladi — ular alohida doiraviy
// chevron tugmalar (prev_page_url/next_page_url) bilan ko'rsatilgani
// uchun, bu yerda faqat sahifa raqamlari qoladi.
const numberedLinks = computed(() => props.staff.links.slice(1, -1))

const statCards = computed(() => [
    { label: 'Professor-o\'qituvchilar', icon: 'mdi:account-group-outline', value: `${props.stats.total ?? 0}+` },
    { label: 'Professorlar',             icon: 'mdi:school-outline',        value: props.stats.professor ?? 0 },
    { label: 'Dotsentlar',               icon: 'mdi:account-tie-outline',   value: props.stats.dotsent ?? 0 },
    { label: 'PhD / DSc',                icon: 'mdi:certificate-outline',   value: props.stats.phd ?? 0 },
])

const search = () => {
    router.get(route('university.about.staff'), { ...form }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    })
}
</script>
