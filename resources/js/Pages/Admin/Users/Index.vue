<template>
    <AppLayout title="Foydalanuvchilar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Foydalanuvchilar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Jami: {{ users.total }} ta foydalanuvchi</p>
                </div>
                <div class="flex items-center gap-3">
                    <button @click="importOpen = true" class="btn-brand-outline">
                        <Icon icon="mdi:file-excel-outline" class="w-4 h-4" />
                        Excel'dan import
                    </button>
                    <Link :href="route('admin.users.create')" class="btn-brand">
                        <Icon icon="mdi:plus" class="w-4 h-4" />
                        Yangi foydalanuvchi
                    </Link>
                </div>
            </div>

            <!-- Import xatoliklari/eslatmalari -->
            <div v-if="importErrors.length" class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-semibold text-amber-800 flex items-center gap-2">
                        <Icon icon="mdi:alert-outline" class="w-4 h-4" />
                        Import paytida {{ importErrors.length }} ta eslatma/muammo
                    </p>
                    <button @click="importErrors = []" class="text-amber-500 hover:text-amber-700">
                        <Icon icon="mdi:close" class="w-4 h-4" />
                    </button>
                </div>
                <ul class="text-xs text-amber-700 space-y-0.5 max-h-40 overflow-y-auto">
                    <li v-for="(err, i) in importErrors" :key="i">{{ err }}</li>
                </ul>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl border border-gray-100 p-4 flex flex-wrap gap-3"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <!-- Search -->
                <div class="flex-1 min-w-48 relative">
                    <Icon icon="mdi:magnify" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Ism yoki email bo'yicha qidirish..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-brand-500 bg-gray-50"
                        @input="debouncedSearch"
                    >
                </div>

                <!-- Role -->
                <select v-model="filters.role"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-brand-500 bg-gray-50"
                        @change="applyFilters">
                    <option value="">Barcha rollar</option>
                    <option v-for="r in roles" :key="r.value" :value="r.value">{{ r.label }}</option>
                </select>

                <!-- Reset -->
                <button v-if="hasFilters" @click="resetFilters"
                        class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 flex items-center gap-1.5">
                    <Icon icon="mdi:close" class="w-4 h-4" />
                    Tozalash
                </button>
            </div>

            <!-- Table — to'liq to'r (grid) chegarali dizayn, referensdagi
                 (billing.e-edu.uz "Shartnoma shablonlari") ko'rinishiga mos:
                 ustunlar orasida ham, qatorlar orasida ham chiziqlar bor.
                 Bu global .table-grid* klasslari (app.css) — shu naqsh
                 boshqa barcha jadval sahifalarida ham qo'llanilishi kerak. -->
            <div class="table-grid-wrap">
                <table class="table-grid">
                    <thead>
                    <tr>
                        <th>Foydalanuvchi</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Oxirgi kirish</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="!users.data?.length">
                        <td colspan="5" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:account-off-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Foydalanuvchi topilmadi</p>
                        </td>
                    </tr>
                    <tr v-for="u in users.data ?? []" :key="u.id">

                        <!-- Avatar + Ism -->
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm text-white flex-shrink-0 bg-brand-600">
                                    {{ u.full_name?.charAt(0)?.toUpperCase() || 'U' }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ u.full_name }}</p>
                                    <p class="text-xs text-gray-400">#{{ u.id }}</p>
                                </div>
                            </div>
                        </td>

                        <!-- Email -->
                        <td class="text-gray-600">{{ u.email }}</td>

                        <!-- Rol -->
                        <td>
                                <span v-for="role in u.roles" :key="role.id"
                                      class="badge-pill mr-1"
                                      :class="roleBadge(role.name)">
                                    {{ roleLabel(role.name) }}
                                </span>
                        </td>

                        <!-- Oxirgi kirish -->
                        <td class="text-xs text-gray-400">
                            {{ u.last_login_at ? formatDate(u.last_login_at) : 'Hali kirmagan' }}
                        </td>

                        <!-- Amallar -->
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <Link
                                    :href="route('admin.users.edit', u.id)"
                                    title="Tahrirlash"
                                    class="btn-ghost-icon">
                                    <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                                </Link>
                                <button
                                    v-if="!u.roles?.some(r => r.name === 'super-admin')"
                                    @click="confirmDelete(u)"
                                    title="O'chirish"
                                    class="btn-ghost-icon danger">
                                    <Icon icon="mdi:delete-outline" class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination — konturli (outline) sahifa raqamlari +
                     "N / page" tanlovchisi, referensdagi kabi. Oldin/Keyingi
                     tugmalari endi Laravel'ning "&laquo; Previous" kabi
                     matn yorlig'i o'rniga sof < > strelka ikonkalarida. -->
                <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-3">
                    <p class="text-xs text-gray-500">Jami {{ users.total }} ta</p>
                    <div class="flex items-center gap-1.5">
                        <template v-for="link in (users.links ?? [])" :key="link.label">
                            <component
                                :is="link.url ? Link : 'span'"
                                :href="link.url ?? undefined"
                                class="pagination-btn"
                                :class="[link.active ? 'active' : '', !link.url ? 'disabled' : '']"
                            >
                                <ChevronLeftIcon v-if="isPrevLabel(link.label)" class="w-4 h-4" />
                                <ChevronRightIcon v-else-if="isNextLabel(link.label)" class="w-4 h-4" />
                                <span v-else v-html="link.label" />
                            </component>
                        </template>

                        <select v-model.number="filters.per_page" @change="applyFilters" class="select-filter ml-1">
                            <option v-for="n in perPageOptions" :key="n" :value="n">{{ n }} / page</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import modal -->
        <div v-if="importOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="closeImport">
            <div class="bg-white rounded-2xl w-full max-w-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-gray-900">Xodimlarni Excel'dan import qilish</h3>
                    <button @click="closeImport" class="text-gray-400 hover:text-gray-600">
                        <Icon icon="mdi:close" class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <a :href="route('admin.users.template')"
                       class="flex items-center gap-2 text-sm font-medium text-brand-600 hover:underline">
                        <Icon icon="mdi:download-outline" class="w-4 h-4" />
                        Namuna shablonni yuklab olish
                    </a>

                    <div>
                        <label class="field-label">Excel fayl (.xlsx, .xls, .csv) <span class="req">*</span></label>
                        <input type="file" accept=".xlsx,.xls,.csv" @change="onFileChange"
                               class="field-input" :class="importForm.errors.file ? 'field-error' : ''">
                        <p v-if="importForm.errors.file" class="err">{{ importForm.errors.file }}</p>
                    </div>

                    <p class="text-xs text-gray-400">
                        Email bo'yicha mavjud xodim topilsa — ma'lumotlari yangilanadi (paroli o'zgarmaydi).
                        Yangi xodim uchun standart parol — Passport seriya raqami (bo'lmasa, avtomatik yaratiladi va natijada ko'rsatiladi).
                    </p>
                </div>

                <div class="flex gap-3 mt-6">
                    <button @click="closeImport" class="btn-neutral flex-1 justify-center">Bekor qilish</button>
                    <button @click="submitImport" :disabled="importForm.processing" class="btn-brand flex-1 justify-center">
                        <Icon v-if="importForm.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                        {{ importForm.processing ? 'Yuklanmoqda...' : 'Import qilish' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete modal -->
        <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="deleteTarget = null">
            <div class="bg-white rounded-2xl w-full max-w-sm p-6">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <Icon icon="mdi:delete-outline" class="w-6 h-6 text-red-500" />
                </div>
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Foydalanuvchini o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ deleteTarget?.full_name }}</strong> ni o'chirasizmi?
                </p>
                <div class="flex gap-3">
                    <button @click="deleteTarget = null" class="btn-neutral flex-1 justify-center">Bekor qilish</button>
                    <button @click="submitDelete" class="btn-danger-pill flex-1">O'chirish</button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { Link, router, useForm, usePage } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    users:   { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
    roles:   { type: Array,  default: () => [] },
    filters: { type: Object, default: () => ({}) },
})

const page = usePage()

const deleteTarget = ref(null)

// Import
const importOpen = ref(false)
const importErrors = ref([...(page.props.flash?.importErrors || [])])

watch(() => page.props.flash?.importErrors, (val) => {
    importErrors.value = [...(val || [])]
})

const importForm = useForm({
    file: null,
})

const onFileChange = (e) => {
    importForm.file = e.target.files[0] || null
}

const closeImport = () => {
    importOpen.value = false
    importForm.reset()
    importForm.clearErrors()
}

const submitImport = () => {
    importForm.post(route('admin.users.import'), {
        forceFormData: true,
        onSuccess: () => { importOpen.value = false; importForm.reset() },
    })
}

// Sahifadagi qatorlar soni — referensdagi kabi tanlanadigan, standart 20.
const perPageOptions = [20, 30, 50, 100, 150, 200]

const filters = ref({
    search:   props.filters.search   || '',
    role:     props.filters.role     || '',
    per_page: Number(props.filters.per_page) || 20,
})

const hasFilters = computed(() => filters.value.search || filters.value.role)

const applyFilters = () => {
    router.get(route('admin.users.index'), filters.value, {
        preserveState: true, replace: true,
    })
}

let searchTimer = null
const debouncedSearch = () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(applyFilters, 400)
}

const resetFilters = () => {
    filters.value = { search: '', role: '', per_page: filters.value.per_page }
    applyFilters()
}

// Laravel'ning standart pagination yorliqlari ("&laquo; Previous",
// "Next &raquo;") o'rniga sof strelka ikonkalarini ko'rsatish uchun.
const isPrevLabel = (label) => /Previous|&laquo;|«/i.test(label)
const isNextLabel = (label) => /Next|&raquo;|»/i.test(label)

const roles = [
    { value: 'super-admin', label: 'Super Admin', class: 'bg-red-50 text-red-700 border-red-200' },
    { value: 'admin',       label: 'Admin',       class: 'bg-purple-50 text-purple-700 border-purple-200' },
    { value: 'admission',   label: 'Qabul',       class: 'badge-brand' },
    { value: 'teacher',     label: "O'qituvchi",  class: 'badge-success' },
    { value: 'tutor',       label: 'Tutor',       class: 'bg-indigo-50 text-indigo-700 border-indigo-200' },
    { value: 'finance',     label: 'Moliya',      class: 'badge-warning' },
    { value: 'librarian',   label: 'Kutubxonachi',class: 'bg-teal-50 text-teal-700 border-teal-200' },
    { value: 'student',     label: 'Talaba',      class: 'badge-neutral' },
]

const roleLabel = (name) => roles.find(r => r.value === name)?.label || name
const roleBadge = (name) => roles.find(r => r.value === name)?.class || 'badge-neutral'

const confirmDelete = (u) => { deleteTarget.value = u }

const submitDelete = () => {
    router.delete(route('admin.users.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null },
    })
}

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('uz-UZ', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    })
}
</script>

<style scoped>
.field-label {
    display: block;
    font-size: 0.78rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.375rem;
}
.req { color: #ef4444; }
.field-input {
    width: 100%;
    padding: 0.6rem 0.875rem;
    border-radius: 0.625rem;
    border: 1.5px solid #e5e7eb;
    font-size: 0.875rem;
    color: #111827;
    background: #fafafa;
    outline: none;
    transition: border-color 0.2s;
}
.field-input:focus { border-color: var(--color-brand-600); background: white; }
.field-error { border-color: #f87171 !important; background: #fef2f2 !important; }
.err { color: #ef4444; font-size: 0.7rem; margin-top: 0.25rem; display: block; }
</style>
