<template>
    <AppLayout title="Kontraktlar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Kontraktlar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Jami: {{ contracts.total }} ta kontrakt</p>
                </div>
                <Link :href="route('admin.contracts.create')" class="btn-brand">
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    Yangi kontrakt
                </Link>
            </div>

            <!-- Stat kartalar -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
                <div v-for="stat in statCards" :key="stat.label"
                     class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1" :style="{ background: stat.color }" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">{{ stat.label }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ stat.value }}</p>
                    </div>
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
                        placeholder="Kontrakt raqami, ism, pasport..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-brand-600 bg-gray-50"
                        @input="debouncedSearch"
                    >
                </div>

                <!-- Status -->
                <select v-model="filters.status" class="select-filter" @change="applyFilters">
                    <option value="">Barcha statuslar</option>
                    <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>

                <!-- Payment type -->
                <select v-model="filters.payment_type" class="select-filter" @change="applyFilters">
                    <option value="">Barcha turlar</option>
                    <option value="grant">Grant</option>
                    <option value="contract">Kontrakt</option>
                </select>

                <!-- Reset -->
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
                        <th>Kontrakt №</th>
                        <th>Abituriyent</th>
                        <th>Yo'nalish</th>
                        <th>Summa</th>
                        <th>Turi</th>
                        <th>Status</th>
                        <th>Sana</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="!contracts.data?.length">
                        <td colspan="8" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:file-document-off-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Kontrakt topilmadi</p>
                        </td>
                    </tr>
                    <tr v-for="c in contracts.data ?? []" :key="c.id">

                        <!-- Kontrakt raqami -->
                        <td>
                            <span class="text-sm font-mono font-semibold text-brand-600">
                                {{ c.contract_number }}
                            </span>
                        </td>

                        <!-- Abituriyent -->
                        <td>
                            <p class="text-sm font-medium text-gray-900">
                                {{ personOf(c)?.last_name }} {{ personOf(c)?.first_name }}
                            </p>
                            <p class="text-xs text-gray-400 font-mono">{{ personOf(c)?.passport_series }}</p>
                        </td>

                        <!-- Yo'nalish -->
                        <td>
                            <p class="text-xs text-gray-700">{{ c.direction?.name_uz || '—' }}</p>
                            <p class="text-xs text-gray-400">{{ c.direction?.faculty?.short_name || '' }}</p>
                        </td>

                        <!-- Summa -->
                        <td>
                            <span class="text-sm font-bold text-gray-800">
                                {{ formatAmount(c.amount) }}
                            </span>
                            <span v-if="c.discount_percent > 0" class="badge-pill badge-brand ml-1">
                                -{{ c.discount_percent }}%
                            </span>
                        </td>

                        <!-- Turi -->
                        <td>
                            <span class="badge-pill" :class="c.payment_type === 'grant' ? 'badge-success' : 'badge-brand'">
                                {{ c.payment_type === 'grant' ? 'Grant' : 'Kontrakt' }}
                            </span>
                        </td>

                        <!-- Status -->
                        <td>
                            <span class="badge-pill" :class="statusBadge(c.status)">
                                {{ statusLabel(c.status) }}
                            </span>
                        </td>

                        <!-- Sana -->
                        <td class="text-xs text-gray-400">
                            {{ formatDate(c.created_at) }}
                        </td>

                        <!-- Amallar -->
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="route('admin.contracts.show', c.id)" title="Ko'rish" class="btn-ghost-icon">
                                    <Icon icon="mdi:eye-outline" class="w-4 h-4" />
                                </Link>
                                <Link :href="route('admin.contracts.edit', c.id)" title="Tahrirlash" class="btn-ghost-icon">
                                    <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                                </Link>
                                <button @click="confirmDelete(c)" title="O'chirish" class="btn-ghost-icon danger">
                                    <Icon icon="mdi:delete-outline" class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="(contracts.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-3">
                    <p class="text-xs text-gray-500">{{ contracts.from }}–{{ contracts.to }} / {{ contracts.total }}</p>
                    <div class="flex items-center gap-1.5">
                        <template v-for="link in (contracts.links ?? [])" :key="link.label">
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

        <!-- Delete modal -->
        <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="deleteTarget = null">
            <div class="bg-white rounded-2xl w-full max-w-sm p-6">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <Icon icon="mdi:delete-outline" class="w-6 h-6 text-red-500" />
                </div>
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Kontraktni o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ deleteTarget?.contract_number }}</strong> kontraktini o'chirasizmi?
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
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    contracts: { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
    filters:   { type: Object, default: () => ({}) },
    stats:     { type: Object, default: () => ({}) },
})

// Kontrakt Abituriyentlar oqimi orqali (applicant) yoki talaba
// to'g'ridan-to'g'ri kiritilganda (student) yaratilgan bo'lishi mumkin
const personOf = (c) => c.applicant ?? c.student

const deleteTarget = ref(null)

const filters = ref({
    search:       props.filters.search       || '',
    status:       props.filters.status       || '',
    payment_type: props.filters.payment_type || '',
})

const hasFilters = computed(() =>
    filters.value.search || filters.value.status || filters.value.payment_type
)

const statCards = computed(() => [
    { label: 'Jami',      value: props.stats.total     || 0, color: 'var(--color-brand-600)' },
    { label: 'Qoralama',  value: props.stats.draft     || 0, color: '#f59e0b' },
    { label: 'Imzolandi', value: props.stats.signed    || 0, color: '#3b82f6' },
    { label: "To'landi",  value: props.stats.paid      || 0, color: '#22c55e' },
    { label: 'Bekor',     value: props.stats.cancelled || 0, color: '#ef4444' },
])

const statuses = [
    { value: 'draft',     label: 'Qoralama',  class: 'badge-warning' },
    { value: 'signed',    label: 'Imzolandi', class: 'badge-brand' },
    { value: 'paid',      label: "To'landi",  class: 'badge-success' },
    { value: 'cancelled', label: 'Bekor',     class: 'badge-danger' },
]

const statusLabel = (s) => statuses.find(x => x.value === s)?.label || s
const statusBadge = (s) => statuses.find(x => x.value === s)?.class || 'badge-neutral'

const applyFilters = () => {
    router.get(route('admin.contracts.index'), filters.value, {
        preserveState: true, replace: true,
    })
}

let searchTimer = null
const debouncedSearch = () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(applyFilters, 400)
}

const resetFilters = () => {
    filters.value = { search: '', status: '', payment_type: '' }
    applyFilters()
}

const confirmDelete = (c) => { deleteTarget.value = c }

const submitDelete = () => {
    router.delete(route('admin.contracts.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null },
    })
}

const formatAmount = (amount) => {
    if (!amount) return '—'
    return new Intl.NumberFormat('uz-UZ').format(amount) + ' so\'m'
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
