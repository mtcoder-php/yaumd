<template>
    <div class="min-h-screen bg-gray-50 flex">

        <!-- Sidebar -->
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 transition-transform duration-200 lg:translate-x-0 lg:static lg:inset-auto"
        >
            <!-- Logo -->
            <div class="h-16 flex items-center px-6 border-b border-gray-200">
                <div>
                    <p class="font-semibold text-gray-900 text-sm">Yangi Asr</p>
                    <p class="text-xs text-gray-400">Universiteti</p>
                </div>
            </div>

            <!-- Nav -->
            <!-- Ko'p elementli bo'limlar (masalan "Boshqaruv", "Moliya") ixcham
                 accordion sifatida ochilib-yopiladi — LEKIN faqat menyusi
                 haqiqatan uzun bo'lgan rollarda (masalan super-admin/admin).
                 Menyusi qisqa bo'lgan rollarda (masalan talaba) accordion
                 keraksiz — bo'lim sarlavhasi oddiy, bosilmaydigan matn
                 sifatida ko'rsatiladi va barcha havolalar doim ochiq turadi
                 (useAccordion'ga qarang). Bitta elementli bo'lim (masalan
                 "Asosiy" ichidagi yolg'iz Dashboard) har doim oddiy,
                 sarlavhasiz havola sifatida qoladi. -->
            <nav class="p-3 space-y-0.5 overflow-y-auto h-[calc(100vh-4rem)]">
                <template v-for="section in groupedMenu" :key="section.label">

                    <template v-if="!useAccordion || section.children.length <= 1">
                        <p v-if="!useAccordion && section.children.length > 1"
                           class="px-3 pt-3 pb-1.5 text-xs font-medium uppercase tracking-wider text-gray-400">
                            {{ section.label }}
                        </p>
                        <Link
                            v-for="item in section.children"
                            :key="item.href"
                            :href="item.href"
                            class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition"
                            :class="isActive(item.href)
                                ? 'bg-gray-900 text-white'
                                : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                        >
                            <component :is="item.icon" class="w-4 h-4 flex-shrink-0" />
                            <span>{{ item.label }}</span>
                        </Link>
                    </template>

                    <div v-else>
                        <button
                            type="button"
                            @click="toggleSection(section.label)"
                            class="w-full flex items-center justify-between gap-2 px-3 pt-3 pb-1.5 text-xs font-medium uppercase tracking-wider transition"
                            :class="isSectionOpen(section) ? 'text-gray-600' : 'text-gray-400 hover:text-gray-600'"
                        >
                            <span>{{ section.label }}</span>
                            <ChevronRightIcon
                                class="w-3.5 h-3.5 flex-shrink-0 transition-transform duration-200"
                                :class="isSectionOpen(section) ? 'rotate-90' : ''"
                            />
                        </button>

                        <div v-show="isSectionOpen(section)" class="space-y-0.5">
                            <Link
                                v-for="item in section.children"
                                :key="item.href"
                                :href="item.href"
                                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition"
                                :class="isActive(item.href)
                                    ? 'bg-gray-900 text-white'
                                    : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                            >
                                <component :is="item.icon" class="w-4 h-4 flex-shrink-0" />
                                <span>{{ item.label }}</span>
                            </Link>
                        </div>
                    </div>

                </template>
            </nav>
        </aside>

        <!-- Overlay (mobile) -->
        <div
            v-if="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-black/20 lg:hidden"
        />

        <!-- Main -->
        <div class="flex-1 flex flex-col min-w-0">

            <!-- Topbar -->
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 sticky top-0 z-30">
                <div class="flex items-center gap-4">
                    <button
                        @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden text-gray-500 hover:text-gray-700"
                    >
                        <Bars3Icon class="w-5 h-5" />
                    </button>
                    <h1 class="text-sm font-medium text-gray-800">{{ title }}</h1>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Joriy rol (faqat ko'rsatish uchun — haqiqiy rol, bazadan) -->
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 text-xs text-gray-600">
                        <ShieldCheckIcon class="w-3.5 h-3.5" />
                        <span>{{ currentRole }}</span>
                    </div>

                    <!-- User -->
                    <div class="relative">
                        <button
                            @click="userMenuOpen = !userMenuOpen"
                            class="flex items-center gap-2.5 hover:opacity-75 transition"
                        >
                            <div v-if="auth.user.photo_url"
                                 class="w-8 h-8 rounded-full bg-cover bg-center border border-gray-200"
                                 :style="`background-image:url('${auth.user.photo_url}')`">
                            </div>
                            <div v-else class="w-8 h-8 rounded-full bg-gray-900 flex items-center justify-center text-white text-xs font-medium">
                                {{ initials }}
                            </div>
                            <div class="hidden sm:block text-left">
                                <p class="text-sm font-medium text-gray-800 leading-none">
                                    {{ auth.user.full_name }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ currentRole }}</p>
                            </div>
                        </button>

                        <div
                            v-if="userMenuOpen"
                            class="absolute right-0 mt-2 w-48 bg-white rounded-xl border border-gray-200 shadow-sm py-1 z-50"
                        >
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-xs text-gray-500">{{ auth.user.email }}</p>
                            </div>
                            <Link
                                :href="route('admin.profile.edit')"
                                class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition"
                            >
                                Mening profilim
                            </Link>
                            <button
                                @click="logout"
                                class="w-full text-left px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition"
                            >
                                Chiqish
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page content -->
            <main class="flex-1 p-6">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import {
    Bars3Icon,
    ShieldCheckIcon,
    Squares2X2Icon,
    UsersIcon,
    AcademicCapIcon,
    BookOpenIcon,
    ClipboardDocumentListIcon,
    CalendarDaysIcon,
    DocumentTextIcon,
    CreditCardIcon,
    BuildingLibraryIcon,
    RectangleStackIcon,
    CommandLineIcon,
    BookmarkSquareIcon,
    UserGroupIcon,
    ChevronRightIcon,
    PhoneIcon,
    ChartBarIcon,
} from '@heroicons/vue/24/outline'

defineProps({
    title: {
        type: String,
        default: 'Dashboard',
    },
})

const page = usePage()
const toast = useToast()

// Flash xabarlarni toastr orqali chiqarish
watch(() => page.props.flash, (flash) => {
    if (flash?.success) toast.success(flash.success)
    if (flash?.error)   toast.error(flash.error)
}, { immediate: true, deep: true })

const sidebarOpen  = ref(false)
const userMenuOpen = ref(false)

const auth = computed(() => page.props.auth)

const ROLE_LABELS = {
    'super-admin': 'Super Admin',
    'admin':       'Admin',
    'admission':   "Qabul xodimi",
    'teacher':     "O'qituvchi",
    'student':     'Talaba',
    'librarian':   'Kutubxonachi',
    'finance':     'Moliya xodimi',
}

// MUHIM: bular faqat qaysi havolalar menyuda ko'rinishini belgilaydi —
// haqiqiy ruxsat har doim serverda (routes/admin.php'dagi `permission:`
// middleware) alohida tekshiriladi. Avval bu yerda "Rolni almashtirish
// (test)" degan sof interfeys almashtirgichi bor edi (localStorage'da
// saqlanardi) — u hisobning haqiqiy roliga aslo ta'sir qilmagani uchun
// chalkashlik keltirib chiqargan. Endi menyu to'g'ridan-to'g'ri haqiqiy
// rol(lar)dan olinadi.
//
// Bir foydalanuvchida BIR NECHTA rol bo'lishi mumkin (masalan "admin" +
// "moliya xodimi") — shuning uchun pastda faqat bitta rolning menyusi emas,
// balki foydalanuvchiga biriktirilgan BARCHA rollarning menyulari
// birlashtiriladi, shunda bitta hisob bilan o'ziga tegishli barcha
// bo'limlarda bitta sahifada ishlash mumkin bo'ladi.
const ROLE_PRIORITY = ['super-admin', 'admin', 'admission', 'teacher', 'finance', 'librarian', 'student']

const userRoles = computed(() => {
    const roles = auth.value?.user?.roles || []
    if (!roles.length) return ['student']
    // Ko'rsatish tartibi barqaror bo'lishi uchun (masalan har doim
    // "Super Admin, Moliya xodimi" — teskarisi emas) ustuvorlik bo'yicha
    // saralanadi; ro'yxatda yo'q rol bo'lsa ham oxiriga qo'shiladi.
    return ROLE_PRIORITY.filter(r => roles.includes(r))
        .concat(roles.filter(r => !ROLE_PRIORITY.includes(r)))
})

const currentRole = computed(() =>
    userRoles.value.map(r => ROLE_LABELS[r] || r).join(', ')
)

const initials = computed(() => {
    const name = auth.value?.user?.full_name || ''
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase()
})

const isActive = (href) => {
    if (!href) return false
    // MUHIM: oddiy startsWith(href) noto'g'ri edi — masalan
    // '/admin/library-categories' manzili '/admin/library' bilan
    // BOSHLANADI, shuning uchun "Kutubxona kategoriyalari" sahifasida
    // "Kitoblar" menyusi ham (noto'g'ri) faol bo'lib ko'rinardi. Endi
    // to'liq mos kelish YOKI keyingi belgi '/' bo'lgandagina (ya'ni
    // haqiqiy pastki sahifa, masalan '/admin/library/5') faol deb
    // hisoblanadi.
    //
    // MUHIM #2: page.url'dagi query-string (masalan '?page=2', filtr
    // parametrlari) va hash HISOBGA OLINMAYDI — aks holda jadvalda
    // "Keyingi" (pagination) bosilganda yoki biror filtr qo'llanganda
    // manzil '/admin/users?page=2' ga aylanadi va yuqoridagi solishtirish
    // ikkalasida ham mos kelmay qoladi (na to'liq teng, na '/' bilan
    // boshlanadi) — natijada bo'lim FAOL EMAS deb hisoblanib, pastdagi
    // watch() uni yopib qo'yar edi (Foydalanuvchilar sahifasida "Next"
    // bosilganda "Boshqaruv" bo'limi kutilmaganda yopilib qolgan edi).
    const path = page.url.split('?')[0].split('#')[0]

    return path === href || path.startsWith(href + '/')
}

const logout = () => {
    userMenuOpen.value = false
    router.post('/logout')
}

// Menyu — haqiqiy rolga qarab
const menus = {
    'super-admin': [
        { type: 'group', label: 'Asosiy' },
        { icon: Squares2X2Icon,            label: 'Dashboard',          href: '/admin/dashboard' },
        { type: 'group', label: 'Boshqaruv' },
        { icon: UsersIcon,                 label: 'Foydalanuvchilar',   href: '/admin/users' },
        { icon: BuildingLibraryIcon,       label: 'Fakultetlar',        href: '/admin/faculties' },
        { icon: AcademicCapIcon,           label: 'Kafedralar',         href: '/admin/departments' },
        { icon: RectangleStackIcon,        label: "Yo'nalishlar",       href: '/admin/directions' },
        { icon: BookOpenIcon,              label: 'Fanlar',             href: '/admin/subjects' },
        { icon: RectangleStackIcon,        label: "Fanlar bloki",       href: '/admin/direction-subjects' },
        { icon: ShieldCheckIcon,           label: 'Audit log',          href: '/admin/audit-logs' },
        { type: 'group', label: 'Talabalar' },
        { icon: CalendarDaysIcon,          label: 'Akademik yillar',    href: '/admin/academic-years' },
        { icon: UserGroupIcon,             label: 'Talabalar',          href: '/admin/students' },
        { icon: BookmarkSquareIcon,        label: 'Guruhlar',           href: '/admin/student-groups' },
        { type: 'group', label: 'Qabul' },
        { icon: ClipboardDocumentListIcon, label: 'Abituriyentlar',     href: '/admin/applicants' },
        { icon: CalendarDaysIcon,          label: 'Suhbatlar',          href: '/admin/interviews' },
        { icon: ClipboardDocumentListIcon, label: 'Test sessiyalari', href: '/admin/test-sessions' },
        { type: 'group', label: 'Moliya' },
        { icon: DocumentTextIcon,          label: 'Kontraktlar',        href: '/admin/contracts' },
        { icon: CreditCardIcon,            label: "To'lovlar",          href: '/admin/payments' },
        { type: 'group', label: 'CRM' },
        { icon: PhoneIcon,                 label: 'Qarzdorlar',         href: '/admin/crm/debtors' },
        { icon: ChartBarIcon,              label: 'CRM hisobotlari',    href: '/admin/crm/reports' },
        { type: 'group', label: "Ta'lim" },
        { icon: RectangleStackIcon,        label: 'Kurs kategoriyalari', href: '/admin/course-categories' },
        { icon: BookOpenIcon,              label: 'Kurslar',            href: '/admin/courses' },
        { type: 'group', label: 'Kutubxona' },
        { icon: BuildingLibraryIcon,       label: 'Kitoblar',           href: '/admin/library' },
        { icon: RectangleStackIcon,        label: 'Kutubxona kategoriyalari', href: '/admin/library-categories' },
    ],
    'admin': [
        { type: 'group', label: 'Asosiy' },
        { icon: Squares2X2Icon,            label: 'Dashboard',          href: '/admin/dashboard' },
        { type: 'group', label: 'Boshqaruv' },
        { icon: UsersIcon,                 label: 'Foydalanuvchilar',   href: '/admin/users' },
        { icon: BuildingLibraryIcon,       label: 'Fakultetlar',        href: '/admin/faculties' },
        { icon: AcademicCapIcon,           label: 'Kafedralar',         href: '/admin/departments' },
        { icon: RectangleStackIcon,        label: "Yo'nalishlar",       href: '/admin/directions' },
        { icon: BookOpenIcon,              label: 'Fanlar',             href: '/admin/subjects' },
        { icon: RectangleStackIcon,        label: "Yo'nalish-fanlar",   href: '/admin/direction-subjects' },
        { type: 'group', label: 'Talabalar' },
        { icon: CalendarDaysIcon,          label: 'Akademik yillar',    href: '/admin/academic-years' },
        { icon: UserGroupIcon,             label: 'Talabalar',          href: '/admin/students' },
        { icon: BookmarkSquareIcon,        label: 'Guruhlar',           href: '/admin/student-groups' },
        { type: 'group', label: 'Qabul' },
        { icon: ClipboardDocumentListIcon, label: 'Abituriyentlar',     href: '/admin/applicants' },
        { icon: CalendarDaysIcon,          label: 'Suhbatlar',          href: '/admin/interviews' },
        { icon: ClipboardDocumentListIcon, label: 'Test sessiyalari', href: '/admin/test-sessions' },
        { type: 'group', label: 'Moliya' },
        { icon: DocumentTextIcon,          label: 'Kontraktlar',        href: '/admin/contracts' },
        { icon: CreditCardIcon,            label: "To'lovlar",          href: '/admin/payments' },
        { type: 'group', label: 'CRM' },
        { icon: PhoneIcon,                 label: 'Qarzdorlar',         href: '/admin/crm/debtors' },
        { icon: ChartBarIcon,              label: 'CRM hisobotlari',    href: '/admin/crm/reports' },
        { type: 'group', label: "Ta'lim" },
        { icon: RectangleStackIcon,        label: 'Kurs kategoriyalari', href: '/admin/course-categories' },
        { icon: BookOpenIcon,              label: 'Kurslar',            href: '/admin/courses' },
        { type: 'group', label: 'Kutubxona' },
        { icon: BuildingLibraryIcon,       label: 'Kitoblar',           href: '/admin/library' },
        { icon: RectangleStackIcon,        label: 'Kutubxona kategoriyalari', href: '/admin/library-categories' },
    ],
    'admission': [
        { type: 'group', label: 'Asosiy' },
        { icon: Squares2X2Icon,            label: 'Dashboard',          href: '/admin/dashboard' },
        { type: 'group', label: 'Qabul' },
        { icon: ClipboardDocumentListIcon, label: 'Abituriyentlar',     href: '/admin/applicants' },
        { icon: CalendarDaysIcon,          label: 'Suhbatlar',          href: '/admin/interviews' },
        { icon: BookOpenIcon,              label: 'Fanlar',             href: '/admin/subjects' },
        { icon: RectangleStackIcon,        label: "Yo'nalish-fanlar",   href: '/admin/direction-subjects' },
        { icon: DocumentTextIcon,          label: 'Kontraktlar',        href: '/admin/contracts' },
    ],
    'teacher': [
        { type: 'group', label: 'Asosiy' },
        { icon: Squares2X2Icon,            label: 'Dashboard',          href: '/admin/dashboard' },
        { type: 'group', label: "Ta'lim" },
        { icon: BookOpenIcon,              label: 'Kurslar',            href: '/admin/courses' },
    ],
    'student': [
        { type: 'group', label: 'Asosiy' },
        { icon: Squares2X2Icon,            label: 'Dashboard',          href: '/admin/dashboard' },
        { type: 'group', label: "Ta'lim" },
        { icon: BookOpenIcon,              label: 'Kurslarim',          href: '/admin/my-courses' },
        { icon: RectangleStackIcon,        label: 'Kurslar katalogi',   href: '/admin/course-catalog' },
        { icon: BuildingLibraryIcon,       label: 'Kutubxona',          href: '/admin/my-library' },
        { icon: DocumentTextIcon,          label: 'Mening shartnomam',  href: '/admin/my-contract' },
    ],
    'librarian': [
        { type: 'group', label: 'Asosiy' },
        { icon: Squares2X2Icon,            label: 'Dashboard',          href: '/admin/dashboard' },
        { type: 'group', label: 'Kutubxona' },
        { icon: BuildingLibraryIcon,       label: 'Kitoblar',           href: '/admin/library' },
        { icon: RectangleStackIcon,        label: 'Kategoriyalar',      href: '/admin/library-categories' },
    ],
    'finance': [
        { type: 'group', label: 'Asosiy' },
        { icon: Squares2X2Icon,            label: 'Dashboard',          href: '/admin/dashboard' },
        { type: 'group', label: 'Moliya' },
        { icon: DocumentTextIcon,          label: 'Kontraktlar',        href: '/admin/contracts' },
        { icon: CreditCardIcon,            label: "To'lovlar",          href: '/admin/payments' },
        { type: 'group', label: 'CRM' },
        { icon: PhoneIcon,                 label: 'Qarzdorlar',         href: '/admin/crm/debtors' },
        { icon: ChartBarIcon,              label: 'CRM hisobotlari',    href: '/admin/crm/reports' },
    ],
}

const menuItems = computed(() => {
    const seen = new Set()
    const merged = []

    userRoles.value.forEach((role) => {
        (menus[role] || []).forEach((item) => {
            // Guruh sarlavhasi ("Moliya" kabi) yoki havola ("/admin/payments"
            // kabi) ikkinchi rolda qayta uchrasa — takrorlanmasin.
            const key = item.type === 'group' ? `group:${item.label}` : `link:${item.href}`
            if (seen.has(key)) return
            seen.add(key)
            merged.push(item)
        })
    })

    return merged.length ? merged : menus['student']
})

// menuItems — tekis ro'yxat ('group' belgisi + undan keyingi havolalar).
// Sidebar'ni ixcham (accordion) qilish uchun shu tekis ro'yxatni
// {label, children: [...]} bo'limlariga guruhlaymiz — "menus" obyektining
// o'zini o'zgartirish shart emas.
const groupedMenu = computed(() => {
    const sections = []
    let current = null

    menuItems.value.forEach((item) => {
        if (item.type === 'group') {
            current = { label: item.label, children: [] }
            sections.push(current)
        } else if (current) {
            current.children.push(item)
        }
    })

    return sections
})

// Accordion faqat menyusi haqiqatan uzun bo'lgan rollarda kerak (masalan
// super-admin/admin — 15+ havola). Menyusida jami shuncha havoladan kam
// bo'lgan rol (masalan talaba — atigi 5 ta) uchun accordion ortiqcha
// murakkablik bo'ladi, shuning uchun bunday hollarda barcha bo'limlar
// doim ochiq, oddiy ro'yxat sifatida ko'rsatiladi.
const ACCORDION_THRESHOLD = 8
const totalLinkCount = computed(() => menuItems.value.filter((item) => item.type !== 'group').length)
const useAccordion = computed(() => totalLinkCount.value > ACCORDION_THRESHOLD)

// Qaysi ko'p elementli bo'limlar hozir ochiq turibdi. Joriy sahifa turgan
// bo'lim avtomatik ochiladi (pastdagi watch), foydalanuvchi istalgan
// bo'limni bosib ochib/yopib turishi mumkin.
const openSections = ref(new Set())

watch(groupedMenu, (sections) => {
    sections.forEach((section) => {
        if (section.children.some((item) => isActive(item.href))) {
            openSections.value.add(section.label)
        }
    })
}, { immediate: true })

const isSectionOpen = (section) => openSections.value.has(section.label)

const toggleSection = (label) => {
    if (openSections.value.has(label)) {
        openSections.value.delete(label)
    } else {
        openSections.value.add(label)
    }
}
</script>
