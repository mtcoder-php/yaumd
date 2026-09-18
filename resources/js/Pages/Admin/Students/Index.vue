<template>
    <AppLayout title="Talabalar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Talabalar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Jami: {{ students.total }} ta talaba</p>
                </div>
                <div class="flex items-center gap-2">
                    <a :href="exportUrl" class="btn-neutral">
                        <Icon icon="mdi:file-download-outline" class="w-4 h-4" />
                        Excel'ga eksport
                    </a>
                    <button @click="importOpen = true" class="btn-brand-outline">
                        <Icon icon="mdi:file-excel-outline" class="w-4 h-4" />
                        HEMIS'dan import
                    </button>
                    <Link :href="route('admin.students.create')" class="btn-brand">
                        <Icon icon="mdi:plus" class="w-4 h-4" />
                        Yangi talaba
                    </Link>
                </div>
            </div>

            <!-- Import xatoliklari -->
            <div v-if="importErrors.length" class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-semibold text-amber-800 flex items-center gap-2">
                        <Icon icon="mdi:alert-outline" class="w-4 h-4" />
                        Import paytida {{ importErrors.length }} ta qatorda muammo topildi
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

                <div class="flex-1 min-w-48 relative">
                    <Icon icon="mdi:magnify" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Ism, familiya, talaba raqami, HEMIS ID..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-brand-600 bg-gray-50"
                        @input="debouncedSearch"
                    >
                </div>

                <select v-model="filters.academic_year_id" @change="applyFilters" class="select-filter">
                    <option value="">Barcha o'quv yillari</option>
                    <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.name }}</option>
                </select>

                <select v-model="filters.direction_id" @change="applyFilters" class="select-filter">
                    <option value="">Barcha yo'nalishlar</option>
                    <option v-for="d in directions" :key="d.id" :value="d.id">{{ d.name_uz }}</option>
                </select>

                <select v-model="filters.course_year" @change="applyFilters" class="select-filter">
                    <option value="">Barcha kurslar</option>
                    <option v-for="c in 6" :key="c" :value="c">{{ c }}-kurs</option>
                </select>

                <select v-model="filters.status" @change="applyFilters" class="select-filter">
                    <option value="">Barcha statuslar</option>
                    <option v-for="s in statusOptions" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>

                <button v-if="hasFilters" @click="resetFilters" class="btn-neutral">
                    <Icon icon="mdi:close" class="w-4 h-4" />
                    Tozalash
                </button>
            </div>

            <!-- Table -->
            <div class="table-grid-wrap">
                <table class="table-grid">
                    <thead>
                    <tr>
                        <th>Talaba</th>
                        <th>Yo'nalish</th>
                        <th>O'quv yili</th>
                        <th class="text-center">Kurs</th>
                        <th>Holati</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="!students.data?.length">
                        <td colspan="6" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:account-school-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Talaba topilmadi</p>
                        </td>
                    </tr>
                    <tr v-for="s in students.data ?? []" :key="s.id">

                        <!-- Talaba -->
                        <td>
                            <Link :href="route('admin.students.show', s.id)" class="flex items-center gap-3 group">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-white text-xs flex-shrink-0 bg-brand-600">
                                    {{ initials(s) }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 group-hover:text-brand-600 transition">{{ fullName(s) }}</p>
                                    <p class="text-xs text-gray-400 font-mono">{{ s.student_number || s.hemis_id || '—' }}</p>
                                </div>
                            </Link>
                        </td>

                        <!-- Yo'nalish -->
                        <td class="text-sm text-gray-600">{{ s.direction?.name_uz || '—' }}</td>

                        <!-- O'quv yili -->
                        <td class="text-sm text-gray-600">{{ s.academic_year?.name || '—' }}</td>

                        <!-- Kurs -->
                        <td class="text-center">
                            <span class="badge-pill badge-brand">{{ s.course_year }}-kurs</span>
                        </td>

                        <!-- Holati -->
                        <td>
                            <span class="badge-pill" :class="statusClass(s.status)">
                                {{ statusLabel(s.status) }}
                            </span>
                        </td>

                        <!-- Amallar -->
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="route('admin.students.show', s.id)" title="Ko'rish" class="btn-ghost-icon">
                                    <Icon icon="mdi:eye-outline" class="w-4 h-4" />
                                </Link>
                                <Link :href="route('admin.students.edit', s.id)" title="Tahrirlash" class="btn-ghost-icon">
                                    <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                                </Link>
                                <button @click="confirmDelete(s)" title="O'chirish" class="btn-ghost-icon danger">
                                    <Icon icon="mdi:delete-outline" class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-3">
                    <p class="text-xs text-gray-500">{{ students.from }}–{{ students.to }} / {{ students.total }}</p>
                    <div class="flex items-center gap-1.5">
                        <template v-for="link in (students.links ?? [])" :key="link.label">
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Import modal -->
        <div v-if="importOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="closeImport">
            <div class="bg-white rounded-2xl w-full max-w-md p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-bold text-gray-900">HEMIS'dan talabalarni import qilish</h3>
                    <button @click="closeImport" class="text-gray-400 hover:text-gray-600">
                        <Icon icon="mdi:close" class="w-5 h-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <a :href="route('admin.students.template')"
                       class="flex items-center gap-2 text-sm font-medium text-brand-600 hover:underline">
                        <Icon icon="mdi:download-outline" class="w-4 h-4" />
                        Namuna shablonni yuklab olish
                    </a>

                    <div>
                        <label class="field-label"><span class="req">*</span> O'quv yili</label>
                        <select v-model="importForm.academic_year_id" class="field-input"
                                :class="importForm.errors.academic_year_id ? 'field-error' : ''">
                            <option value="">Tanlang</option>
                            <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.name }}</option>
                        </select>
                        <p v-if="importForm.errors.academic_year_id" class="err">{{ importForm.errors.academic_year_id }}</p>
                    </div>

                    <div>
                        <label class="field-label"><span class="req">*</span> Excel fayl (.xlsx, .xls, .csv)</label>
                        <input type="file" accept=".xlsx,.xls,.csv" @change="onFileChange"
                               class="field-input" :class="importForm.errors.file ? 'field-error' : ''">
                        <p v-if="importForm.errors.file" class="err">{{ importForm.errors.file }}</p>
                    </div>
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
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Talabani o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ fullName(deleteTarget) }}</strong>ni o'chirasizmi?
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
    students: {
        type: Object,
        default: () => ({ data: [], links: [], total: 0, from: 0, to: 0, last_page: 1 }),
    },
    academicYears: { type: Array, default: () => [] },
    directions:    { type: Array, default: () => [] },
    filters:       { type: Object, default: () => ({}) },
})

const page = usePage()

const statusOptions = [
    { value: 'active',         label: "O'qimoqda" },
    { value: 'academic_leave', label: 'Akademik ta\'til' },
    { value: 'expelled',       label: 'Chetlashtirilgan' },
    { value: 'graduated',      label: 'Bitirgan' },
    { value: 'transferred',    label: "Ko'chirilgan" },
]

const statusLabel = (v) => statusOptions.find(s => s.value === v)?.label || v
const statusClass = (v) => ({
    active:         'badge-success',
    academic_leave: 'badge-warning',
    expelled:       'badge-danger',
    graduated:      'badge-brand',
    transferred:    'badge-neutral',
}[v] || 'badge-neutral')

const fullName = (s) => [s.last_name, s.first_name, s.middle_name].filter(Boolean).join(' ')
const initials = (s) => [s.last_name, s.first_name].filter(Boolean).map(n => n[0]).join('').toUpperCase()

// Filtrlash
const filters = ref({
    search:            props.filters.search            || '',
    academic_year_id:  props.filters.academic_year_id  || '',
    direction_id:      props.filters.direction_id      || '',
    course_year:       props.filters.course_year       || '',
    status:            props.filters.status            || '',
})

const hasFilters = computed(() => Object.values(filters.value).some(v => v))

// Joriy qidiruv/filtrlar bilan bir xil natijani eksport qilish uchun —
// bu oddiy fayl yuklab olish (Inertia navigatsiyasi emas), shuning uchun
// <Link>/router.get emas, oddiy <a href> ishlatiladi.
const exportUrl = computed(() => {
    const params = new URLSearchParams(
        Object.entries(filters.value).filter(([, v]) => v !== '' && v !== null)
    )
    const query = params.toString()
    return route('admin.students.export') + (query ? `?${query}` : '')
})

const applyFilters = () => {
    router.get(route('admin.students.index'), filters.value, {
        preserveState: true,
        replace: true,
    })
}

let searchTimer = null
const debouncedSearch = () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(applyFilters, 400)
}

const resetFilters = () => {
    filters.value = { search: '', academic_year_id: '', direction_id: '', course_year: '', status: '' }
    applyFilters()
}

// Import
const importOpen = ref(false)
const importErrors = ref([...(page.props.flash?.importErrors || [])])

watch(() => page.props.flash?.importErrors, (val) => {
    importErrors.value = [...(val || [])]
})

const importForm = useForm({
    academic_year_id: '',
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
    importForm.post(route('admin.students.import'), {
        forceFormData: true,
        onSuccess: () => { importOpen.value = false; importForm.reset() },
    })
}

// O'chirish
const deleteTarget = ref(null)
const confirmDelete = (s) => { deleteTarget.value = s }
const submitDelete = () => {
    router.delete(route('admin.students.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null },
    })
}

// Laravel'ning standart pagination yorliqlari o'rniga sof strelka
// ikonkalarini ko'rsatish uchun (Foydalanuvchilar sahifasidagi bilan
// bir xil naqsh).
const isPrevLabel = (label) => /Previous|&laquo;|«/i.test(label)
const isNextLabel = (label) => /Next|&raquo;|»/i.test(label)
</script>

<style scoped>
.field-label {
    display: block;
    font-size: 0.78rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 0.375rem;
}
.req { color: #ef4444; margin-right: 0.15rem; }
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
    appearance: auto;
}
.field-input:focus { border-color: var(--color-brand-600); background: white; }
.field-error { border-color: #f87171 !important; background: #fef2f2 !important; }
.err { color: #ef4444; font-size: 0.7rem; margin-top: 0.25rem; display: block; }
</style>
