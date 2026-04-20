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
            <nav class="p-4 space-y-1 overflow-y-auto h-[calc(100vh-4rem)]">
                <template v-for="item in menuItems" :key="item.label">
                    <p
                        v-if="item.type === 'group'"
                        class="text-xs font-medium text-gray-400 uppercase tracking-wider px-3 pt-4 pb-1"
                    >
                        {{ item.label }}
                    </p>
                    <Link
                        v-else
                        :href="item.href"
                        class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition"
                        :class="isActive(item.href)
                            ? 'bg-gray-900 text-white'
                            : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900'"
                    >
                        <component
                            :is="item.icon"
                            class="w-4 h-4 flex-shrink-0"
                        />
                        <span>{{ item.label }}</span>
                    </Link>
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
                    <!-- Rol almashtirgich -->
                    <div class="relative">
                        <button
                            @click="roleSwitcherOpen = !roleSwitcherOpen"
                            class="flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 hover:bg-gray-50 transition text-xs text-gray-600"
                        >
                            <ShieldCheckIcon class="w-3.5 h-3.5" />
                            <span>{{ currentRole }}</span>
                            <ChevronDownIcon class="w-3 h-3" />
                        </button>

                        <div
                            v-if="roleSwitcherOpen"
                            class="absolute right-0 mt-2 w-52 bg-white rounded-xl border border-gray-200 shadow-sm py-1 z-50"
                        >
                            <p class="text-xs text-gray-400 px-4 py-2 border-b border-gray-100">
                                Rolni almashtirish (test)
                            </p>
                            <button
                                v-for="r in allRoles"
                                :key="r.value"
                                @click="switchRole(r.value)"
                                class="w-full text-left px-4 py-2 text-sm transition flex items-center justify-between"
                                :class="activeRole === r.value
                                    ? 'text-gray-900 font-medium bg-gray-50'
                                    : 'text-gray-600 hover:bg-gray-50'"
                            >
                                <span>{{ r.label }}</span>
                                <CheckIcon v-if="activeRole === r.value" class="w-3.5 h-3.5 text-gray-900" />
                            </button>
                        </div>
                    </div>

                    <!-- User -->
                    <div class="relative">
                        <button
                            @click="userMenuOpen = !userMenuOpen"
                            class="flex items-center gap-2.5 hover:opacity-75 transition"
                        >
                            <div class="w-8 h-8 rounded-full bg-gray-900 flex items-center justify-center text-white text-xs font-medium">
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
import { ref, computed } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import {
    Bars3Icon,
    ChevronDownIcon,
    CheckIcon,
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
} from '@heroicons/vue/24/outline'

defineProps({
    title: {
        type: String,
        default: 'Dashboard',
    },
})

const page = usePage()
const auth = computed(() => page.props.auth)

const sidebarOpen  = ref(false)
const userMenuOpen = ref(false)
const roleSwitcherOpen = ref(false)

// Rol almashtirgich — localStorage da saqlanadi
const activeRole = ref(localStorage.getItem('devRole') || auth.value.user?.roles?.[0] || 'super-admin')

const allRoles = [
    { value: 'super-admin', label: 'Super Admin' },
    { value: 'admin',       label: 'Admin' },
    { value: 'admission',   label: "Qabul xodimi" },
    { value: 'teacher',     label: "O'qituvchi" },
    { value: 'student',     label: 'Talaba' },
    { value: 'librarian',   label: 'Kutubxonachi' },
    { value: 'finance',     label: 'Moliya xodimi' },
]

const currentRole = computed(() =>
    allRoles.find(r => r.value === activeRole.value)?.label || activeRole.value
)

const switchRole = (role) => {
    activeRole.value = role
    localStorage.setItem('devRole', role)
    roleSwitcherOpen.value = false
}

const initials = computed(() => {
    const name = auth.value.user?.full_name || ''
    return name.split(' ').map(n => n[0]).slice(0, 2).join('').toUpperCase()
})

const isActive = (href) => page.url.startsWith(href)

const logout = () => {
    userMenuOpen.value = false
    localStorage.removeItem('devRole')
    router.post('/logout')
}

// Menyu — rolga qarab
const menus = {
    'super-admin': [
        { type: 'group', label: 'Asosiy' },
        { icon: Squares2X2Icon,            label: 'Dashboard',       href: '/dashboard' },
        { type: 'group', label: 'Boshqaruv' },
        { icon: UsersIcon,                 label: 'Foydalanuvchilar', href: '/users' },
        { icon: AcademicCapIcon,           label: 'Fakultetlar',      href: '/faculties' },
        { icon: RectangleStackIcon,        label: "Yo'nalishlar",     href: '/directions' },
        { type: 'group', label: 'Qabul' },
        { icon: ClipboardDocumentListIcon, label: 'Abituriyentlar',   href: '/applicants' },
        { icon: CalendarDaysIcon,          label: 'Suhbatlar',        href: '/interviews' },
        { icon: DocumentTextIcon,          label: 'Testlar',          href: '/tests' },
        { type: 'group', label: 'Moliya' },
        { icon: DocumentTextIcon,          label: 'Kontraktlar',      href: '/contracts' },
        { icon: CreditCardIcon,            label: "To'lovlar",        href: '/payments' },
        { type: 'group', label: "Ta'lim" },
        { icon: BookOpenIcon,              label: 'Kurslar',          href: '/courses' },
        { icon: BuildingLibraryIcon,       label: 'Kutubxona',        href: '/library' },
    ],
    'admin': [
        { type: 'group', label: 'Asosiy' },
        { icon: Squares2X2Icon,            label: 'Dashboard',       href: '/dashboard' },
        { type: 'group', label: 'Boshqaruv' },
        { icon: UsersIcon,                 label: 'Foydalanuvchilar', href: '/users' },
        { icon: AcademicCapIcon,           label: 'Fakultetlar',      href: '/faculties' },
        { icon: RectangleStackIcon,        label: "Yo'nalishlar",     href: '/directions' },
        { type: 'group', label: 'Qabul' },
        { icon: ClipboardDocumentListIcon, label: 'Abituriyentlar',   href: '/applicants' },
        { icon: CalendarDaysIcon,          label: 'Suhbatlar',        href: '/interviews' },
        { icon: DocumentTextIcon,          label: 'Testlar',          href: '/tests' },
        { type: 'group', label: 'Moliya' },
        { icon: DocumentTextIcon,          label: 'Kontraktlar',      href: '/contracts' },
        { icon: CreditCardIcon,            label: "To'lovlar",        href: '/payments' },
        { type: 'group', label: "Ta'lim" },
        { icon: BookOpenIcon,              label: 'Kurslar',          href: '/courses' },
        { icon: BuildingLibraryIcon,       label: 'Kutubxona',        href: '/library' },
    ],
    'admission': [
        { type: 'group', label: 'Asosiy' },
        { icon: Squares2X2Icon,            label: 'Dashboard',       href: '/dashboard' },
        { type: 'group', label: 'Qabul' },
        { icon: ClipboardDocumentListIcon, label: 'Abituriyentlar',   href: '/applicants' },
        { icon: CalendarDaysIcon,          label: 'Suhbatlar',        href: '/interviews' },
        { icon: DocumentTextIcon,          label: 'Testlar',          href: '/tests' },
        { icon: DocumentTextIcon,          label: 'Kontraktlar',      href: '/contracts' },
    ],
    'teacher': [
        { type: 'group', label: 'Asosiy' },
        { icon: Squares2X2Icon, label: 'Dashboard', href: '/dashboard' },
        { type: 'group', label: "Ta'lim" },
        { icon: BookOpenIcon,   label: 'Kurslar',   href: '/courses' },
    ],
    'student': [
        { type: 'group', label: 'Asosiy' },
        { icon: Squares2X2Icon,      label: 'Dashboard',  href: '/dashboard' },
        { type: 'group', label: "Ta'lim" },
        { icon: BookOpenIcon,        label: 'Kurslarim',  href: '/courses' },
        { icon: BuildingLibraryIcon, label: 'Kutubxona',  href: '/library' },
    ],
    'librarian': [
        { type: 'group', label: 'Asosiy' },
        { icon: Squares2X2Icon,      label: 'Dashboard', href: '/dashboard' },
        { type: 'group', label: 'Kutubxona' },
        { icon: BuildingLibraryIcon, label: 'Kitoblar',  href: '/library' },
    ],
    'finance': [
        { type: 'group', label: 'Asosiy' },
        { icon: Squares2X2Icon,     label: 'Dashboard',   href: '/dashboard' },
        { type: 'group', label: 'Moliya' },
        { icon: DocumentTextIcon,   label: 'Kontraktlar', href: '/contracts' },
        { icon: CreditCardIcon,     label: "To'lovlar",   href: '/payments' },
    ],
}

const menuItems = computed(() => menus[activeRole.value] || menus['super-admin'])
</script>
