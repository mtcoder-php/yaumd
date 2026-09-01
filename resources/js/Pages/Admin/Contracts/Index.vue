<template>
    <AppLayout title="Kontraktlar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Kontraktlar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Jami: {{ contracts.total }} ta kontrakt</p>
                </div>
                <Link :href="route('admin.contracts.create')" class="btn-primary">
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    Yangi kontrakt
                </Link>
            </div>

            <!-- Stat kartalar -->
            <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
                <div v-for="stat in statCards" :key="stat.label"
                     class="bg-white rounded-xl border border-gray-100 p-4 text-center"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <p class="text-2xl font-bold" :style="{ color: stat.color }">{{ stat.value }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ stat.label }}</p>
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
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50"
                        @input="debouncedSearch"
                    >
                </div>

                <!-- Status -->
                <select v-model="filters.status"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50"
                        @change="applyFilters">
                    <option value="">Barcha statuslar</option>
                    <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>

                <!-- Payment type -->
                <select v-model="filters.payment_type"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50"
                        @change="applyFilters">
                    <option value="">Barcha turlar</option>
                    <option value="grant">Grant</option>
                    <option value="contract">Kontrakt</option>
                </select>

                <!-- Reset -->
                <button v-if="hasFilters" @click="resetFilters"
                        class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 flex items-center gap-1.5">
                    <Icon icon="mdi:close" class="w-4 h-4" />
                    Tozalash
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Kontrakt №</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Abituriyent</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Yo'nalish</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Summa</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Turi</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Sana</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                        <tr v-if="!contracts.data?.length">
                            <td colspan="8" class="text-center py-16 text-gray-400">
                                <Icon icon="mdi:file-document-off-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                                <p class="text-sm">Kontrakt topilmadi</p>
                            </td>
                        </tr>
                        <tr v-for="c in contracts.data ?? []" :key="c.id"
                            class="hover:bg-gray-50 transition-colors">

                            <!-- Kontrakt raqami -->
                            <td class="px-4 py-3">
                                    <span class="text-sm font-mono font-semibold text-[#0f3460]">
                                        {{ c.contract_number }}
                                    </span>
                            </td>

                            <!-- Abituriyent -->
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ personOf(c)?.last_name }} {{ personOf(c)?.first_name }}
                                </p>
                                <p class="text-xs text-gray-400 font-mono">{{ personOf(c)?.passport_series }}</p>
                            </td>

                            <!-- Yo'nalish -->
                            <td class="px-4 py-3">
                                <p class="text-xs text-gray-700">{{ c.direction?.name_uz || '—' }}</p>
                                <p class="text-xs text-gray-400">{{ c.direction?.faculty?.short_name || '' }}</p>
                            </td>

                            <!-- Summa -->
                            <td class="px-4 py-3">
                                    <span class="text-sm font-bold text-gray-800">
                                        {{ formatAmount(c.amount) }}
                                    </span>
                            </td>

                            <!-- Turi -->
                            <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                          :class="c.payment_type === 'grant'
                                            ? 'bg-green-50 text-green-700'
                                            : 'bg-blue-50 text-blue-700'">
                                        {{ c.payment_type === 'grant' ? 'Grant' : 'Kontrakt' }}
                                    </span>
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                          :class="statusBadge(c.status)">
                                        {{ statusLabel(c.status) }}
                                    </span>
                            </td>

                            <!-- Sana -->
                            <td class="px-4 py-3 text-xs text-gray-400">
                                {{ formatDate(c.created_at) }}
                            </td>

                            <!-- Amallar -->
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <Link
                                        :href="route('admin.contracts.show', c.id)"
                                        class="text-xs font-medium text-[#0f3460] hover:text-[#533483] flex items-center gap-1">
                                        <Icon icon="mdi:eye-outline" class="w-3.5 h-3.5" />
                                        Ko'rish
                                    </Link>
                                    <Link
                                        :href="route('admin.contracts.edit', c.id)"
                                        class="inline-flex items-center gap-1 text-xs font-medium text-amber-600 hover:text-amber-800 transition"
                                    >
                                        <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                                        Tahrir
                                    </Link>
                                    <button @click="confirmDelete(c)"
                                            class="text-xs font-medium text-red-500 hover:text-red-700 flex items-center gap-1">
                                        <Icon icon="mdi:delete-outline" class="w-3.5 h-3.5" />
                                        O'chirish
                                    </button>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="(contracts.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500">{{ contracts.from }}–{{ contracts.to }} / {{ contracts.total }}</p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in (contracts.links ?? [])" :key="link.label">
                            <Link v-if="link.url" :href="link.url"
                                  class="px-3 py-1.5 text-xs rounded-lg transition"
                                  :class="link.active ? 'text-white font-semibold' : 'text-gray-500 hover:bg-gray-100'"
                                  :style="link.active ? 'background:linear-gradient(135deg,#0f3460,#533483)' : ''"
                                  v-html="link.label" />
                            <span v-else class="px-3 py-1.5 text-xs text-gray-300" v-html="link.label" />
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
                    <button @click="deleteTarget = null" class="btn-secondary flex-1">Bekor qilish</button>
                    <button @click="submitDelete" class="btn-danger flex-1">O'chirish</button>
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
    { label: 'Jami',      value: props.stats.total     || 0, color: '#0f3460' },
    { label: 'Qoralama',  value: props.stats.draft     || 0, color: '#f59e0b' },
    { label: 'Imzolandi', value: props.stats.signed    || 0, color: '#3b82f6' },
    { label: "To'landi",  value: props.stats.paid      || 0, color: '#22c55e' },
    { label: 'Bekor',     value: props.stats.cancelled || 0, color: '#ef4444' },
])

const statuses = [
    { value: 'draft',     label: 'Qoralama',  class: 'bg-yellow-50 text-yellow-700' },
    { value: 'signed',    label: 'Imzolandi', class: 'bg-blue-50 text-blue-700' },
    { value: 'paid',      label: "To'landi",  class: 'bg-green-50 text-green-700' },
    { value: 'cancelled', label: 'Bekor',     class: 'bg-red-50 text-red-700' },
]

const statusLabel = (s) => statuses.find(x => x.value === s)?.label || s
const statusBadge = (s) => statuses.find(x => x.value === s)?.class || 'bg-gray-100 text-gray-600'

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
</script>

<style scoped>
.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border-radius: 0.75rem;
    background: linear-gradient(135deg, #0f3460, #533483);
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}
.btn-primary:hover { box-shadow: 0 6px 20px rgba(15,52,96,0.3); }

.btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.625rem 1.25rem;
    border-radius: 0.75rem;
    background: white;
    color: #374151;
    font-size: 0.875rem;
    font-weight: 600;
    border: 1.5px solid #e5e7eb;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-secondary:hover { background: #f9fafb; }

.btn-danger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.625rem 1.25rem;
    border-radius: 0.75rem;
    background: #ef4444;
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
}
.btn-danger:hover { background: #dc2626; }
</style>
