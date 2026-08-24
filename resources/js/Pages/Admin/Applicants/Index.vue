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
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50"
                        @input="debouncedSearch"
                    >
                </div>

                <!-- Status filter -->
                <select
                    v-model="filters.status"
                    class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50"
                    @change="applyFilters"
                >
                    <option value="">Barcha statuslar</option>
                    <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>

                <!-- Education type filter -->
                <select
                    v-model="filters.education_type"
                    class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50"
                    @change="applyFilters"
                >
                    <option value="">Barcha turlar</option>
                    <option value="bachelor">Bakalavr</option>
                    <option value="master">Magistr</option>
                    <option value="transfer">Transfer</option>
                    <option value="second">2-mutaxassislik</option>
                </select>

                <!-- Reset -->
                <button
                    v-if="hasFilters"
                    @click="resetFilters"
                    class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 flex items-center gap-1.5"
                >
                    <Icon icon="mdi:close" class="w-4 h-4" />
                    Tozalash
                </button>
            </div>

            <!-- Bulk action panel -->
            <div
                v-if="selectedIds.length"
                class="bg-white rounded-2xl border border-[#0f3460] p-4 flex items-center gap-4"
                style="box-shadow: 0 2px 8px rgba(15,52,96,0.1)"
            >
                <span class="text-sm font-semibold text-[#0f3460]">
                    {{ selectedIds.length }} ta tanlandi
                </span>

                <select
                    v-model="bulkStatus"
                    class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50"
                >
                    <option value="">Status tanlang</option>
                    <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>

                <button
                    @click="applyBulkStatus"
                    :disabled="!bulkStatus"
                    class="px-4 py-2 rounded-xl text-sm font-semibold text-white transition flex items-center gap-2"
                    style="background: linear-gradient(135deg, #0f3460, #533483)"
                    :class="!bulkStatus ? 'opacity-50 cursor-not-allowed' : ''"
                >
                    <Icon icon="mdi:check-all" class="w-4 h-4" />
                    Statusni o'zgartirish
                </button>

                <button
                    @click="selectedIds = []"
                    class="ml-auto text-sm text-gray-400 hover:text-gray-600 flex items-center gap-1"
                >
                    <Icon icon="mdi:close" class="w-4 h-4" />
                    Bekor qilish
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                        <tr class="border-b border-gray-100">
                            <!-- Select all checkbox -->
                            <th class="px-4 py-3 w-10">
                                <input
                                    type="checkbox"
                                    class="rounded"
                                    :checked="isAllSelected"
                                    :indeterminate="isIndeterminate"
                                    @change="toggleSelectAll"
                                >
                            </th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Ariza №</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">F.I.Sh</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Yo'nalish</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Ta'lim turi</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Telefon</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Sana</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                        <tr v-if="!applicants.data?.length">
                            <td colspan="9" class="text-center py-16 text-gray-400">
                                <Icon icon="mdi:file-search-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                                <p class="text-sm">Ariza topilmadi</p>
                            </td>
                        </tr>
                        <tr
                            v-for="a in applicants.data ?? []"
                            :key="a.id"
                            class="hover:bg-gray-50 transition-colors"
                            :class="selectedIds.includes(a.id) ? 'bg-blue-50' : ''"
                        >
                            <!-- Checkbox -->
                            <td class="px-4 py-3">
                                <input
                                    type="checkbox"
                                    class="rounded"
                                    :checked="selectedIds.includes(a.id)"
                                    @change="toggleSelect(a.id)"
                                >
                            </td>

                            <!-- Ariza raqami -->
                            <td class="px-4 py-3">
                                <span class="text-sm font-mono font-semibold text-[#0f3460]">{{ a.application_number }}</span>
                            </td>

                            <!-- FISh -->
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">{{ a.last_name }} {{ a.first_name }}</p>
                                <p class="text-xs text-gray-400">{{ a.middle_name }}</p>
                            </td>

                            <!-- Yo'nalish -->
                            <td class="px-4 py-3">
                                <p class="text-sm text-gray-700">{{ a.direction?.name_uz || '—' }}</p>
                                <p class="text-xs text-gray-400">{{ a.direction?.faculty?.short_name || '' }}</p>
                            </td>

                            <!-- Ta'lim turi -->
                            <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :class="educationTypeBadge(a.education_type)">
                                        {{ educationTypeLabel(a.education_type) }}
                                    </span>
                            </td>

                            <!-- Telefon -->
                            <td class="px-4 py-3 text-sm text-gray-600">{{ a.phone }}</td>

                            <!-- Status -->
                            <td class="px-4 py-3">
                                <select
                                    :value="a.status"
                                    class="text-xs px-2 py-1 rounded-lg border font-medium cursor-pointer"
                                    :class="statusBadge(a.status)"
                                    @change="updateStatus(a.id, $event.target.value)"
                                >
                                    <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                                </select>
                            </td>

                            <!-- Sana -->
                            <td class="px-4 py-3 text-xs text-gray-400">
                                {{ formatDate(a.created_at) }}
                            </td>

                            <!-- Ko'rish / Tahrirlash -->
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <Link
                                        :href="route('admin.applicants.show', a.id)"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-[#0f3460] hover:text-[#533483] transition"
                                    >
                                        <Icon icon="mdi:eye-outline" class="w-4 h-4" />
                                        Ko'rish
                                    </Link>
                                    <Link
                                        :href="route('admin.applicants.edit', a.id)"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-amber-600 hover:text-amber-800 transition"
                                    >
                                        <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                                        Tahrir
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="(applicants.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500">
                        {{ applicants.from }}–{{ applicants.to }} / {{ applicants.total }}
                    </p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in (applicants.links ?? [])" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1.5 text-xs rounded-lg transition"
                                :class="link.active ? 'text-white font-semibold' : 'text-gray-500 hover:bg-gray-100'"
                                :style="link.active ? 'background:linear-gradient(135deg,#0f3460,#533483)' : ''"
                                v-html="link.label"
                            />
                            <span v-else class="px-3 py-1.5 text-xs text-gray-300" v-html="link.label" />
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
    { value: 'new',        label: 'Yangi',            class: 'bg-blue-50 text-blue-700 border-blue-200' },
    { value: 'accepted',   label: 'Qabul qilindi',    class: 'bg-green-50 text-green-700 border-green-200' },
    { value: 'interview',  label: 'Suhbat',           class: 'bg-yellow-50 text-yellow-700 border-yellow-200' },
    { value: 'tested',     label: 'Test',             class: 'bg-purple-50 text-purple-700 border-purple-200' },
    { value: 'contracted', label: 'Kontrakt',         class: 'bg-indigo-50 text-indigo-700 border-indigo-200' },
    { value: 'enrolled',   label: "Ro'yxatga olindi", class: 'bg-teal-50 text-teal-700 border-teal-200' },
    { value: 'rejected',   label: 'Rad etildi',       class: 'bg-red-50 text-red-700 border-red-200' },
]

const statusBadge = (status) =>
    statuses.find(s => s.value === status)?.class || 'bg-gray-50 text-gray-600 border-gray-200'

const educationTypeLabel = (type) => {
    const types = { bachelor: 'Bakalavr', master: 'Magistr', transfer: 'Transfer', second: '2-mutaxassislik' }
    return types[type] || type
}

const educationTypeBadge = (type) => {
    const badges = {
        bachelor: 'bg-blue-50 text-blue-700',
        master:   'bg-purple-50 text-purple-700',
        transfer: 'bg-orange-50 text-orange-700',
        second:   'bg-teal-50 text-teal-700',
    }
    return badges[type] || 'bg-gray-50 text-gray-600'
}

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('uz-UZ', {
        day: '2-digit', month: '2-digit', year: 'numeric'
    })
}
</script>
