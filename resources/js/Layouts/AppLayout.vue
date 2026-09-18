<template>
    <div class="min-h-screen bg-gray-50 flex">

        <!-- Sidebar — HAR DOIM ekran balandligiga TENG va qotirilgan (fixed):
             o'ng tomondagi kontent qanchalik uzun bo'lmasin, sidebar undan
             cho'zilmaydi va faqat o'zining nav qismi (flex-1 overflow-y-auto)
             ichida aylanadi. Avval "lg:static" bo'lgani uchun desktopda
             sidebar oddiy flex-elementga aylanib, konteyner balandligi
             (ya'ni uzun kontent balandligi) bo'yicha cho'zilib ketardi —
             shu sabab bilan olib tashlandi. Endi doim "fixed", shuning
             uchun o'ng tomondagi <main> ustuni mos lg:ml-* bilan siljitiladi.

             Desktopda "yig'ish" (faqat ikonkalar) rejimi bor:
             sidebarCollapsed shuni boshqaradi (yodda saqlanadi, localStorage).
             Mobil rejimda (lg dan kichik) yig'ish tushunchasi yo'q — u yerda
             faqat sidebarOpen (to'liq kenglikda kirib-chiqish) ishlaydi. -->
        <aside
            :class="[
                sidebarOpen ? 'translate-x-0' : '-translate-x-full',
                sidebarCollapsed ? 'lg:w-20' : 'lg:w-80',
            ]"
            class="fixed inset-y-0 left-0 z-50 w-80 bg-white border-r border-gray-200 transition-all duration-200 lg:translate-x-0 flex flex-col"
        >
            <!-- Logo — brend indigo fon (billing.e-edu.uz namunasidagi kabi).
                 Yig'ilgan holatda to'liq matn o'rniga faqat universitet
                 belgisi (ikonka) ko'rsatiladi. -->
            <div class="h-16 flex items-center justify-center bg-brand-600 flex-shrink-0 px-2">
                <img v-if="sidebarCollapsed" src="/assets/logo-icon.png" alt="Yangi Asr Universiteti" class="w-9 h-9 flex-shrink-0" />
                <div v-else class="text-center">
                    <p class="font-bold text-white text-2xl leading-none">edu.yangi-asr.uz</p>
<!--                    <p class="text-sm font-semibold text-brand-100 mt-1">Universiteti</p>-->
                </div>
            </div>

            <!-- Nav — referensdagi (billing.e-edu.uz) uslub: har bir bo'lim
                 ikonka + nom + pastga qaragan strelka (chevron) bilan oddiy
                 menyu qatori sifatida ko'rsatiladi (kichik uppercase yorliq
                 EMAS). Bosilganda bo'lim ochiladi, ichidagi havolalar bir oz
                 chapdan siljigan holda (chiziqcha "−" bilan, ikonkasiz)
                 ko'rsatiladi. Ochiq bo'lim nomi va ikonkasi indigo rangga
                 o'tadi (fon TO'LDIRILMAYDI — faqat matn rangi o'zgaradi).
                 Faol (joriy sahifa) havola esa to'liq indigo pill fonda, oq
                 matn bilan ko'rsatiladi. Faqat BITTA havolali bo'lim (masalan
                 "Asosiy" ichidagi yolg'iz Dashboard) sarlavhasiz, oddiy
                 havola sifatida qoladi.

                 Yig'ilgan holatda (sidebarCollapsed) faqat ikonkalar
                 ko'rsatiladi (matn/chevron yashiriladi, hover'da title
                 sifatida chiqadi); ko'p elementli bo'lim ustiga sichqoncha
                 olib borilsa (yoki bosilsa) — sidebar KENGAYMAYDI, o'rniga
                 ikonka yonida suzuvchi (flyout) kichik menyu chiqadi
                 (pastdagi Teleport'ga qarang — nav'ning o'zi overflow-y-auto
                 bo'lgani uchun uning ICHIDA absolute qo'yilgan flyout
                 kesilib qolar edi, shuning uchun <body>ga teleport qilinib,
                 ekran koordinatalari bo'yicha joylashtiriladi). -->
            <nav class="flex-1 overflow-y-auto p-3 space-y-0.5">
                <template v-for="section in groupedMenu" :key="section.label">

                    <template v-if="section.children.length <= 1">
                        <Link
                            v-for="item in section.children"
                            :key="item.href"
                            :href="item.href"
                            :title="sidebarCollapsed ? item.label : null"
                            class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-[15px] font-semibold transition"
                            :class="[
                                isActive(item.href) ? 'bg-brand-600 text-white' : 'text-gray-700 hover:bg-gray-50',
                                sidebarCollapsed ? 'justify-center px-0' : '',
                            ]"
                        >
                            <component :is="item.icon" class="w-[18px] h-[18px] flex-shrink-0" />
                            <span v-if="!sidebarCollapsed">{{ item.label }}</span>
                        </Link>
                    </template>

                    <div v-else class="relative">
                        <button
                            type="button"
                            @click="onSectionButtonClick(section, $event)"
                            @mouseenter="openFlyout(section, $event)"
                            @mouseleave="scheduleCloseFlyout"
                            :title="sidebarCollapsed ? section.label : null"
                            class="w-full flex items-center gap-2 px-3 py-2.5 rounded-lg text-[15px] font-semibold transition"
                            :class="[
                                (isSectionOpen(section) && !sidebarCollapsed) || (sidebarCollapsed && flyoutSection === section.label)
                                    ? 'text-brand-600 bg-brand-50'
                                    : 'text-gray-700 hover:bg-gray-50',
                                sidebarCollapsed ? 'justify-center px-0' : 'justify-between',
                            ]"
                        >
                            <span class="flex items-center gap-3">
                                <component :is="section.icon" class="w-[18px] h-[18px] flex-shrink-0" />
                                <span v-if="!sidebarCollapsed">{{ section.label }}</span>
                            </span>
                            <ChevronDownIcon
                                v-if="!sidebarCollapsed"
                                class="w-4 h-4 flex-shrink-0 transition-transform duration-200"
                                :class="isSectionOpen(section) ? 'rotate-180' : 'text-gray-400'"
                            />
                        </button>

                        <!-- Yig'ilmagan sidebar uchun — oddiy ochiladigan ro'yxat -->
                        <div v-show="isSectionOpen(section) && !sidebarCollapsed" class="mt-0.5 space-y-0.5">
                            <Link
                                v-for="item in section.children"
                                :key="item.href"
                                :href="item.href"
                                class="flex items-center gap-2 pl-9 pr-3 py-2 rounded-lg text-sm font-medium transition"
                                :class="isActive(item.href)
                                    ? 'bg-brand-600 text-white font-semibold'
                                    : 'text-gray-600 hover:bg-gray-50'"
                            >
                                <span class="text-xs" :class="isActive(item.href) ? 'text-white' : 'text-gray-400'">−</span>
                                <span>{{ item.label }}</span>
                            </Link>
                        </div>
                    </div>

                </template>
            </nav>

            <!-- Yig'ish/ochish paneli — referensdagi kabi pastda, to'liq
                 kenglikda, brend rangida. Faqat desktopda ko'rinadi. -->
            <button
                type="button"
                @click="sidebarCollapsed = !sidebarCollapsed"
                :title="sidebarCollapsed ? 'Sidebarni ochish' : 'Sidebarni yig\'ish'"
                class="hidden lg:flex items-center justify-center h-11 flex-shrink-0 bg-brand-600 text-white hover:bg-brand-700 transition"
            >
                <ChevronDoubleLeftIcon v-if="!sidebarCollapsed" class="w-10 h-10" />
                <ChevronDoubleRightIcon v-else class="w-10 h-10" />
            </button>
        </aside>

        <!-- Yig'ilgan sidebar uchun suzuvchi (flyout) kichik menyu — <body>ga
             teleport qilinadi, chunki sidebar/nav'ning o'zi overflow-y-auto
             (kesib qo'yadi) va tor (80px), flyout esa undan tashqariga
             chiqishi kerak. Joylashuvi ikonka tugmasining haqiqiy ekran
             koordinatalari bo'yicha hisoblanadi (openFlyout'ga qarang). -->
        <Teleport to="body">
            <div
                v-if="sidebarCollapsed && flyoutSection"
                :style="{ top: flyoutPos.top + 'px', left: flyoutPos.left + 'px' }"
                class="fixed z-[60] w-56 bg-white rounded-xl border border-gray-200 shadow-lg py-2"
                @mouseenter="cancelCloseFlyout"
                @mouseleave="scheduleCloseFlyout"
            >
                <p class="px-4 pb-1.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">{{ flyoutSection }}</p>
                <Link
                    v-for="item in flyoutChildren"
                    :key="item.href"
                    :href="item.href"
                    @click="flyoutSection = null"
                    class="flex items-center gap-2 px-4 py-2 text-sm font-medium transition"
                    :class="isActive(item.href)
                        ? 'bg-brand-600 text-white font-semibold'
                        : 'text-gray-600 hover:bg-gray-50'"
                >
                    <span class="text-xs" :class="isActive(item.href) ? 'text-white' : 'text-gray-400'">−</span>
                    <span>{{ item.label }}</span>
                </Link>
            </div>
        </Teleport>

        <!-- Overlay (mobile) -->
        <div
            v-if="sidebarOpen"
            @click="sidebarOpen = false"
            class="fixed inset-0 z-40 bg-black/20 lg:hidden"
        />

        <!-- Main — sidebar endi "fixed" bo'lgani uchun (flow'dan chiqarilgan),
             uning kengligicha chapdan bo'sh joy qoldiriladi (lg:ml-*), aks
             holda kontent sidebar OSTIDA boshlanib qolar edi. -->
        <div
            :class="sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-80'"
            class="flex-1 flex flex-col min-w-0 transition-all duration-200"
        >

            <!-- Topbar — "sticky" o'rniga to'g'ridan-to'g'ri "fixed": sahifa
                 uzun bo'lganda ba'zi holatlarda sticky "ota" elementning
                 balandligiga bog'liq qolib, pastga scroll qilinganda yuqoriga
                 birga ko'tarilib ketishi mumkin edi. Endi doim ekran
                 yuqorisida qotirilgan, chap chegarasi sidebar kengligiga mos
                 keladi (lg:left-*), pastdagi <main> esa shuncha joy
                 (pt-16) bilan boshlanadi. -->
            <header
                :class="sidebarCollapsed ? 'lg:left-20' : 'lg:left-80'"
                class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 fixed top-0 left-0 right-0 z-30 transition-all duration-200">
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
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full border border-brand-200 bg-brand-50 text-xs text-brand-700 font-medium">
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
                            <div v-else class="w-8 h-8 rounded-full bg-brand-600 flex items-center justify-center text-white text-xs font-medium">
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

            <!-- Page content — pt-16 header endi "fixed" bo'lgani uchun
                 flow'dan chiqib ketgan joyni qoplaydi. -->
            <main class="flex-1 p-6 pt-[calc(1.5rem+4rem)]">
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
    ChevronDownIcon,
    ChevronDoubleLeftIcon,
    ChevronDoubleRightIcon,
    PhoneIcon,
    ChartBarIcon,
    TrophyIcon,
    FingerPrintIcon,
    Cog6ToothIcon,
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

// Sidebar "yig'ish" (faqat ikonkalar) holati — faqat desktop uchun.
// Foydalanuvchining tanlovi localStorage'da saqlanadi, shunda sahifa
// yangilanganda yoki keyingi safar kirganda ham o'sha holat qoladi.
let initialCollapsed = false
try {
    initialCollapsed = localStorage.getItem('yaumd_sidebar_collapsed') === '1'
} catch (e) {
    // localStorage yopiq bo'lishi mumkin (masalan maxfiy rejim) — bunda
    // shunchaki standart (ochiq) holatda ishlayveradi.
}
const sidebarCollapsed = ref(initialCollapsed)

watch(sidebarCollapsed, (val) => {
    try {
        localStorage.setItem('yaumd_sidebar_collapsed', val ? '1' : '0')
    } catch (e) {
        // e'tiborsiz qoldiriladi
    }
})

const auth = computed(() => page.props.auth)

const ROLE_LABELS = {
    'super-admin': 'Super Admin',
    'admin':       'Admin',
    'admission':   "Qabul xodimi",
    'teacher':     "O'qituvchi",
    'tutor':       'Tutor',
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
const ROLE_PRIORITY = ['super-admin', 'admin', 'admission', 'teacher', 'tutor', 'finance', 'librarian', 'student']

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
        { type: 'group', label: 'Boshqaruv', icon: Cog6ToothIcon },
        { icon: UsersIcon,                 label: 'Foydalanuvchilar',   href: '/admin/users' },
        { icon: BuildingLibraryIcon,       label: 'Fakultetlar',        href: '/admin/faculties' },
        { icon: AcademicCapIcon,           label: 'Kafedralar',         href: '/admin/departments' },
        { icon: RectangleStackIcon,        label: "Yo'nalishlar",       href: '/admin/directions' },
        { icon: BookOpenIcon,              label: 'Fanlar',             href: '/admin/subjects' },
        { icon: RectangleStackIcon,        label: "Fanlar bloki",       href: '/admin/direction-subjects' },
        { icon: ShieldCheckIcon,           label: 'Audit log',          href: '/admin/audit-logs' },
        { type: 'group', label: 'Talabalar', icon: AcademicCapIcon },
        { icon: CalendarDaysIcon,          label: 'Akademik yillar',    href: '/admin/academic-years' },
        { icon: UserGroupIcon,             label: 'Talabalar',          href: '/admin/students' },
        { icon: BookmarkSquareIcon,        label: 'Guruhlar',           href: '/admin/student-groups' },
        { type: 'group', label: 'Qabul', icon: ClipboardDocumentListIcon },
        { icon: ClipboardDocumentListIcon, label: 'Abituriyentlar',     href: '/admin/applicants' },
        { icon: CalendarDaysIcon,          label: 'Suhbatlar',          href: '/admin/interviews' },
        { icon: ClipboardDocumentListIcon, label: 'Test sessiyalari', href: '/admin/test-sessions' },
        { type: 'group', label: 'Moliya', icon: CreditCardIcon },
        { icon: DocumentTextIcon,          label: 'Kontraktlar',        href: '/admin/contracts' },
        { icon: CreditCardIcon,            label: "To'lovlar",          href: '/admin/payments' },
        { type: 'group', label: 'CRM', icon: PhoneIcon },
        { icon: PhoneIcon,                 label: 'Qarzdorlar',         href: '/admin/crm/debtors' },
        { icon: ChartBarIcon,              label: 'CRM hisobotlari',    href: '/admin/crm/reports' },
        { icon: TrophyIcon,                label: 'Tutor KPI',          href: '/admin/crm/tutor-kpi' },
        { icon: FingerPrintIcon,           label: 'Turniket moslashtirish', href: '/admin/turnstile/matches' },
        { type: 'group', label: "Ta'lim", icon: BookOpenIcon },
        { icon: RectangleStackIcon,        label: 'Kurs kategoriyalari', href: '/admin/course-categories' },
        { icon: BookOpenIcon,              label: 'Kurslar',            href: '/admin/courses' },
        { type: 'group', label: 'Kutubxona', icon: BuildingLibraryIcon },
        { icon: BuildingLibraryIcon,       label: 'Kitoblar',           href: '/admin/library' },
        { icon: RectangleStackIcon,        label: 'Kutubxona kategoriyalari', href: '/admin/library-categories' },
    ],
    'admin': [
        { type: 'group', label: 'Asosiy' },
        { icon: Squares2X2Icon,            label: 'Dashboard',          href: '/admin/dashboard' },
        { type: 'group', label: 'Boshqaruv', icon: Cog6ToothIcon },
        { icon: UsersIcon,                 label: 'Foydalanuvchilar',   href: '/admin/users' },
        { icon: BuildingLibraryIcon,       label: 'Fakultetlar',        href: '/admin/faculties' },
        { icon: AcademicCapIcon,           label: 'Kafedralar',         href: '/admin/departments' },
        { icon: RectangleStackIcon,        label: "Yo'nalishlar",       href: '/admin/directions' },
        { icon: BookOpenIcon,              label: 'Fanlar',             href: '/admin/subjects' },
        { icon: RectangleStackIcon,        label: "Yo'nalish-fanlar",   href: '/admin/direction-subjects' },
        { type: 'group', label: 'Talabalar', icon: AcademicCapIcon },
        { icon: CalendarDaysIcon,          label: 'Akademik yillar',    href: '/admin/academic-years' },
        { icon: UserGroupIcon,             label: 'Talabalar',          href: '/admin/students' },
        { icon: BookmarkSquareIcon,        label: 'Guruhlar',           href: '/admin/student-groups' },
        { type: 'group', label: 'Qabul', icon: ClipboardDocumentListIcon },
        { icon: ClipboardDocumentListIcon, label: 'Abituriyentlar',     href: '/admin/applicants' },
        { icon: CalendarDaysIcon,          label: 'Suhbatlar',          href: '/admin/interviews' },
        { icon: ClipboardDocumentListIcon, label: 'Test sessiyalari', href: '/admin/test-sessions' },
        { type: 'group', label: 'Moliya', icon: CreditCardIcon },
        { icon: DocumentTextIcon,          label: 'Kontraktlar',        href: '/admin/contracts' },
        { icon: CreditCardIcon,            label: "To'lovlar",          href: '/admin/payments' },
        { type: 'group', label: 'CRM', icon: PhoneIcon },
        { icon: PhoneIcon,                 label: 'Qarzdorlar',         href: '/admin/crm/debtors' },
        { icon: ChartBarIcon,              label: 'CRM hisobotlari',    href: '/admin/crm/reports' },
        { icon: TrophyIcon,                label: 'Tutor KPI',          href: '/admin/crm/tutor-kpi' },
        { icon: FingerPrintIcon,           label: 'Turniket moslashtirish', href: '/admin/turnstile/matches' },
        { type: 'group', label: "Ta'lim", icon: BookOpenIcon },
        { icon: RectangleStackIcon,        label: 'Kurs kategoriyalari', href: '/admin/course-categories' },
        { icon: BookOpenIcon,              label: 'Kurslar',            href: '/admin/courses' },
        { type: 'group', label: 'Kutubxona', icon: BuildingLibraryIcon },
        { icon: BuildingLibraryIcon,       label: 'Kitoblar',           href: '/admin/library' },
        { icon: RectangleStackIcon,        label: 'Kutubxona kategoriyalari', href: '/admin/library-categories' },
    ],
    'admission': [
        { type: 'group', label: 'Asosiy' },
        { icon: Squares2X2Icon,            label: 'Dashboard',          href: '/admin/dashboard' },
        { type: 'group', label: 'Qabul', icon: ClipboardDocumentListIcon },
        { icon: ClipboardDocumentListIcon, label: 'Abituriyentlar',     href: '/admin/applicants' },
        { icon: CalendarDaysIcon,          label: 'Suhbatlar',          href: '/admin/interviews' },
        { icon: BookOpenIcon,              label: 'Fanlar',             href: '/admin/subjects' },
        { icon: RectangleStackIcon,        label: "Yo'nalish-fanlar",   href: '/admin/direction-subjects' },
        { icon: DocumentTextIcon,          label: 'Kontraktlar',        href: '/admin/contracts' },
    ],
    'teacher': [
        { type: 'group', label: 'Asosiy' },
        { icon: Squares2X2Icon,            label: 'Dashboard',          href: '/admin/dashboard' },
        { type: 'group', label: "Ta'lim", icon: BookOpenIcon },
        { icon: BookOpenIcon,              label: 'Kurslar',            href: '/admin/courses' },
    ],
    'student': [
        { type: 'group', label: 'Asosiy' },
        { icon: Squares2X2Icon,            label: 'Dashboard',          href: '/admin/dashboard' },
        { type: 'group', label: "Ta'lim", icon: BookOpenIcon },
        { icon: BookOpenIcon,              label: 'Kurslarim',          href: '/admin/my-courses' },
        { icon: RectangleStackIcon,        label: 'Kurslar katalogi',   href: '/admin/course-catalog' },
        { icon: BuildingLibraryIcon,       label: 'Kutubxona',          href: '/admin/my-library' },
        { icon: DocumentTextIcon,          label: 'Mening shartnomam',  href: '/admin/my-contract' },
    ],
    'librarian': [
        { type: 'group', label: 'Asosiy' },
        { icon: Squares2X2Icon,            label: 'Dashboard',          href: '/admin/dashboard' },
        { type: 'group', label: 'Kutubxona', icon: BuildingLibraryIcon },
        { icon: BuildingLibraryIcon,       label: 'Kitoblar',           href: '/admin/library' },
        { icon: RectangleStackIcon,        label: 'Kategoriyalar',      href: '/admin/library-categories' },
    ],
    'finance': [
        { type: 'group', label: 'Asosiy' },
        { icon: Squares2X2Icon,            label: 'Dashboard',          href: '/admin/dashboard' },
        { type: 'group', label: 'Moliya', icon: CreditCardIcon },
        { icon: DocumentTextIcon,          label: 'Kontraktlar',        href: '/admin/contracts' },
        { icon: CreditCardIcon,            label: "To'lovlar",          href: '/admin/payments' },
        { type: 'group', label: 'CRM', icon: PhoneIcon },
        { icon: PhoneIcon,                 label: 'Qarzdorlar',         href: '/admin/crm/debtors' },
        { icon: ChartBarIcon,              label: 'CRM hisobotlari',    href: '/admin/crm/reports' },
        { icon: TrophyIcon,                label: 'Tutor KPI',          href: '/admin/crm/tutor-kpi' },
    ],
    'tutor': [
        { type: 'group', label: 'Asosiy', icon: Squares2X2Icon },
        { icon: Squares2X2Icon,            label: 'Dashboard',          href: '/admin/dashboard' },
        { icon: TrophyIcon,                label: 'Mening KPI\'m',      href: '/admin/my-kpi' },
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
            current = { label: item.label, icon: item.icon, children: [] }
            sections.push(current)
        } else if (current) {
            current.children.push(item)
        }
    })

    return sections
})

// Qaysi ko'p elementli bo'limlar hozir ochiq turibdi. Joriy sahifa turgan
// bo'lim avtomatik ochiladi (pastdagi watch), foydalanuvchi istalgan
// bo'limni bosib ochib/yopib turishi mumkin.
//
// MUHIM: AppLayout har bir sahifada <AppLayout>...</AppLayout> sifatida
// qo'llanilgani uchun (Inertia "persistent layout" emas), har safar
// sahifa almashtirilganda BUTUN komponent qayta yaratiladi — demak
// openSections ham har safar bo'sh Set'dan boshlanardi va faqat YANGI
// sahifaning bo'limi ochilardi, boshqa avval ochilgan bo'limlar esa
// "o'zi yopilib qolgandek" ko'rinardi (aslida yopilmagan, shunchaki
// butunlay unutilgan edi). Buni tuzatish uchun openSections ham xuddi
// sidebarCollapsed kabi localStorage'da saqlanadi — shunda sahifadan
// sahifaga o'tilganda avval ochilgan bo'limlar OCHIQ qolaveradi va
// faqat foydalanuvchi o'zi bosib yopganda yopiladi.
let initialOpenSections = new Set()
try {
    const stored = localStorage.getItem('yaumd_sidebar_open_sections')
    if (stored) initialOpenSections = new Set(JSON.parse(stored))
} catch (e) {
    // localStorage yopiq bo'lishi mumkin — bo'sh Set bilan davom etiladi
}
const openSections = ref(initialOpenSections)

watch(openSections, (val) => {
    try {
        localStorage.setItem('yaumd_sidebar_open_sections', JSON.stringify([...val]))
    } catch (e) {
        // e'tiborsiz qoldiriladi
    }
}, { deep: true })

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

// --- Yig'ilgan sidebar uchun suzuvchi (flyout) kichik menyu ---
// Sidebar "yig'ilgan" (faqat ikonkalar) holatida ko'p elementli bo'lim
// ustiga sichqoncha olib borilsa (yoki bosilsa) — sidebar KENGAYMAYDI,
// o'rniga o'sha ikonka yonida (o'ng tomonida) kichik ro'yxat suziб chiqadi.
// flyoutSection — hozir qaysi bo'lim ochiq (label) yoki hech biri (null).
// flyoutPos — flyout'ning ekrandagi aniq koordinatasi (tugmaning
// getBoundingClientRect()'idan hisoblanadi, chunki flyout <body>ga
// teleport qilingan — sidebar/nav ichidagi CSS pozitsiyalardan foydalana
// olmaydi).
const flyoutSection = ref(null)
const flyoutPos = ref({ top: 0, left: 0 })
let flyoutCloseTimer = null

const flyoutChildren = computed(() => {
    const section = groupedMenu.value.find((s) => s.label === flyoutSection.value)
    return section ? section.children : []
})

const openFlyout = (section, event) => {
    if (!sidebarCollapsed.value) return
    clearTimeout(flyoutCloseTimer)
    const rect = event.currentTarget.getBoundingClientRect()
    flyoutPos.value = { top: rect.top, left: rect.right + 8 }
    flyoutSection.value = section.label
}

// Sichqoncha tugmadan ham, flyout'ning o'zidan ham chiqib ketganda —
// darhol emas, ozgina kechikish bilan yopiladi (shu oraliqda foydalanuvchi
// tugmadan flyout'ga o'tib ulguradi, aks holda orasidagi bo'shliqda
// "mouseleave" ishlab, flyout darhol yopilib qolar edi).
const scheduleCloseFlyout = () => {
    clearTimeout(flyoutCloseTimer)
    flyoutCloseTimer = setTimeout(() => {
        flyoutSection.value = null
    }, 200)
}

const cancelCloseFlyout = () => {
    clearTimeout(flyoutCloseTimer)
}

// Sidebar yig'ilmagan holatda — oddiy accordion (ochish/yopish). Yig'ilgan
// holatda esa bosish shart emas (hover kifoya), lekin sichqonchasiz
// (klaviatura/tegishga asoslangan) foydalanish uchun bosish ham flyout'ni
// ochib-yopib turadi.
const onSectionButtonClick = (section, event) => {
    if (!sidebarCollapsed.value) {
        toggleSection(section.label)
        return
    }
    if (flyoutSection.value === section.label) {
        flyoutSection.value = null
    } else {
        openFlyout(section, event)
    }
}

// Sidebar ochilganda (kengaytirilganda) ochiq turgan flyout ma'nosiz
// qoladi — tozalab qo'yamiz.
watch(sidebarCollapsed, (collapsed) => {
    if (!collapsed) flyoutSection.value = null
})
</script>
