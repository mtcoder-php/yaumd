<template>
    <AppLayout title="Abituriyentlar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Abituriyentlar</h1>
                    <p class="text-sm text-gray-500 mt-1">Jami: {{ applicants.total }} ta ariza</p>
                </div>
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
                        placeholder="Ism, pasport, telefon..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-brand-600 bg-gray-50"
                        @input="debouncedSearch"
                    >
                </div>

                <!-- Status filter -->
                <select v-model="filters.status" class="select-filter" @change="applyFilters">
                    <option value="">Barcha statuslar</option>
                    <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>

                <!-- Education type filter -->
                <select v-model="filters.education_type" class="select-filter" @change="applyFilters">
                    <option value="">Barcha turlar</option>
                    <option value="bachelor">Bakalavr</option>
                    <option value="master">Magistr</option>
                    <option value="transfer">Transfer</option>
                    <option value="second">2-mutaxassislik</option>
                </select>

                <!-- Reset -->
                <button v-if="hasFilters" @click="resetFilters" class="btn-neutral">
                    <Icon icon="mdi:close" class="w-4 h-4" />
                    Tozalash
                </button>
            </div>

            <!-- Bulk action panel -->
            <div v-if="selectedIds.length"
                 class="bg-white rounded-2xl border-2 p-4 flex items-center gap-4 flex-wrap"
                 style="border-color: var(--color-brand-200); box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <span class="text-sm font-semibold text-brand-600">
                    {{ selectedIds.length }} ta tanlandi
                </span>

                <select v-model="bulkStatus" class="select-filter">
                    <option value="">Status tanlang</option>
                    <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>

                <button @click="applyBulkStatus" :disabled="!bulkStatus" class="btn-brand">
                    <Icon icon="mdi:check-all" class="w-4 h-4" />
                    Statusni o'zgartirish
                </button>

                <button @click="selectedIds = []" class="ml-auto text-sm text-gray-400 hover:text-gray-600 flex items-center gap-1">
                    <Icon icon="mdi:close" class="w-4 h-4" />
                    Bekor qilish
                </button>
            </div>

            <!-- Table -->
            <div class="table-grid-wrap">
                <table class="table-grid">
                    <thead>
                    <tr>
                        <th class="w-10">
                            <input
                                type="checkbox"
                                class="rounded"
                                :checked="isAllSelected"
                                :indeterminate="isIndeterminate"
                                @change="toggleSelectAll"
                            >
                        </th>
                        <th>Ariza №</th>
                        <th>F.I.Sh</th>
                        <th>Yo'nalish</th>
                        <th>Ta'lim turi</th>
                        <th>Telefon</th>
                        <th>Status</th>
                        <th>Sana</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="!applicants.data?.length">
                        <td colspan="9" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:file-search-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Ariza topilmadi</p>
                        </td>
                    </tr>
                    <tr v-for="a in applicants.data ?? []" :key="a.id" :class="selectedIds.includes(a.id) ? 'bg-brand-50' : ''">

                        <!-- Checkbox -->
                        <td>
                            <input
                                type="checkbox"
                                class="rounded"
                                :checked="selectedIds.includes(a.id)"
                                @change="toggleSelect(a.id)"
                            >
                        </td>

                        <!-- Ariza raqami -->
                        <td>
                            <span class="text-sm font-mono font-semibold text-brand-600">{{ a.application_number }}</span>
                        </td>

                        <!-- FISh -->
                        <td>
                            <p class="text-sm font-medium text-gray-900">{{ a.last_name }} {{ a.first_name }}</p>
                            <p class="text-xs text-gray-400">{{ a.middle_name }}</p>
                        </td>

                        <!-- Yo'nalish -->
                        <td>
                            <p class="text-sm text-gray-700">{{ a.direction?.name_uz || '—' }}</p>
                            <p class="text-xs text-gray-400">{{ a.direction?.faculty?.short_name || '' }}</p>
                        </td>

                        <!-- Ta'lim turi -->
                        <td>
                            <span class="badge-pill" :class="educationTypeBadge(a.education_type)">
                                {{ educationTypeLabel(a.education_type) }}
                            </span>
                        </td>

                        <!-- Telefon -->
                        <td class="text-sm text-gray-600">{{ a.phone }}</td>

                        <!-- Status -->
                        <td>
                            <select
                                :value="a.status"
                                class="badge-pill status-select"
                                :class="statusBadge(a.status)"
                                @change="updateStatus(a.id, $event.target.value)"
                            >
                                <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                            </select>
                        </td>

                        <!-- Sana -->
                        <td class="text-xs text-gray-400">
                            {{ formatDate(a.created_at) }}
                        </td>

                        <!-- Ko'rish / Tahrirlash -->
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="route('admin.applicants.show', a.id)" title="Ko'rish" class="btn-ghost-icon">
                                    <Icon icon="mdi:eye-outline" class="w-4 h-4" />
                                </Link>
                                <Link :href="route('admin.applicants.edit', a.id)" title="Tahrirlash" class="btn-ghost-icon">
                                    <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                                </Link>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="(applicants.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-3">
                    <p class="text-xs text-gray-500">{{ applicants.from }}–{{ applicants.to }} / {{ applicants.total }}</p>
                    <div class="flex items-center gap-1.5">
                        <template v-for="link in (applicants.links ?? [])" :key="link.label">
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
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    applicants: {
        type: Object,
        default: () => ({
            data: [], links: [], total: 0, from: 0, to: 0, last_page: 1, current_page: 1,
        })
    },
    filters: { type: Object, default: () => ({}) },
})

const selectedIds = ref([])
const bulkStatus  = ref('')

const filters = ref({
    search:         props.filters.search         || '',
    status:         props.filters.status         || '',
    education_type: props.filters.education_type || '',
})

const hasFilters = computed(() =>
    filters.value.search || filters.value.status || filters.value.education_type
)

// Checkbox logika
const isAllSelected = computed(() =>
    props.applicants.data?.length > 0 &&
    props.applicants.data.every(a => selectedIds.value.includes(a.id))
)

const isIndeterminate = computed(() =>
    selectedIds.value.length > 0 && !isAllSelected.value
)

const toggleSelect = (id) => {
    if (selectedIds.value.includes(id)) {
        selectedIds.value = selectedIds.value.filter(i => i !== id)
    } else {
        selectedIds.value.push(id)
    }
}

const toggleSelectAll = () => {
    if (isAllSelected.value) {
        selectedIds.value = []
    } else {
        selectedIds.value = props.applicants.data.map(a => a.id)
    }
}

// Bulk status
const applyBulkStatus = () => {
    if (!bulkStatus.value || !selectedIds.value.length) return

    router.patch(route('admin.applicants.bulk-status'), {
        ids:    selectedIds.value,
        status: bulkStatus.value,
    }, {
        onSuccess: () => {
            selectedIds.value = []
            bulkStatus.value  = ''
        },
    })
}

// Filters
const applyFilters = () => {
    router.get(route('admin.applicants.index'), filters.value, {
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
    filters.value = { search: '', status: '', education_type: '' }
    applyFilters()
}

const updateStatus = (id, status) => {
    router.patch(route('admin.applicants.status', id), { status }, {
        preserveState: true,
        preserveScroll: true,
    })
}

const statuses = [
    { value: 'new',        label: 'Yangi',            class: 'badge-neutral' },
    { value: 'accepted',   label: 'Qabul qilindi',    class: 'badge-brand' },
    { value: 'interview',  label: 'Suhbat',           class: 'badge-warning' },
    { value: 'tested',     label: 'Test',             class: 'badge-warning' },
    { value: 'contracted', label: 'Kontrakt',         class: 'badge-brand' },
    { value: 'enrolled',   label: "Ro'yxatga olindi", class: 'badge-success' },
    { value: 'rejected',   label: 'Rad etildi',       class: 'badge-danger' },
]

const statusBadge = (status) =>
    statuses.find(s => s.value === status)?.class || 'badge-neutral'

const educationTypeLabel = (type) => {
    const types = { bachelor: 'Bakalavr', master: 'Magistr', transfer: 'Transfer', second: '2-mutaxassislik' }
    return types[type] || type
}

const educationTypeBadge = (type) => {
    const badges = {
        bachelor: 'badge-brand',
        master:   'badge-warning',
        transfer: 'badge-neutral',
        second:   'badge-success',
    }
    return badges[type] || 'badge-neutral'
}

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('uz-UZ', {
        day: '2-digit', month: '2-digit', year: 'numeric'
    })
}

// Laravel'ning standart pagination yorliqlari o'rniga sof strelka
// ikonkalarini ko'rsatish uchun (Foydalanuvchilar sahifasidagi bilan
// bir xil naqsh).
const isPrevLabel = (label) => /Previous|&laquo;|«/i.test(label)
const isNextLabel = (label) => /Next|&raquo;|»/i.test(label)
</script>

<style scoped>
.status-select {
    cursor: pointer;
    border: 1px solid transparent;
    outline: none;
}
.status-select.badge-brand   { border-color: var(--color-brand-200); }
.status-select.badge-success { border-color: #a7f3d0; }
.status-select.badge-neutral { border-color: #e5e7eb; }
.status-select.badge-warning { border-color: #fde68a; }
.status-select.badge-danger  { border-color: #fecaca; }
</style>
