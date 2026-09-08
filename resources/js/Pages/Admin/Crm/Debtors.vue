<template>
    <AppLayout title="Qarzdorlar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Qarzdorlar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Kontrakt bo'yicha qarzi qolgan abituriyent/talabalar</p>
                </div>
                <a :href="exportUrl" class="btn-secondary">
                    <Icon icon="mdi:file-download-outline" class="w-4 h-4" />
                    Excel'ga eksport
                </a>
            </div>

            <!-- Stat kartalar -->
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-white rounded-xl border border-gray-100 p-4"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <p class="text-2xl font-bold text-gray-900">{{ totals.count }}</p>
                    <p class="text-xs text-gray-400 mt-1">Qarzdorlar soni</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <p class="text-2xl font-bold text-amber-600">{{ formatAmount(totals.amount) }}</p>
                    <p class="text-xs text-gray-400 mt-1">Jami qarz summasi</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl border border-gray-100 p-4 flex flex-wrap gap-3"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <div class="flex-1 min-w-48 relative">
                    <Icon icon="mdi:magnify" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Ism, familiya, kontrakt raqami..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50"
                        @input="debouncedSearch"
                    >
                </div>

                <select v-model="filters.sort" @change="applyFilters"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50">
                    <option value="remaining_desc">Qarz: ko'pdan kamga</option>
                    <option value="remaining_asc">Qarz: kamdan ko'pga</option>
                    <option value="signed_at_desc">Sana: yangidan eskiga</option>
                    <option value="signed_at_asc">Sana: eskidan yangiga</option>
                </select>

                <button v-if="filters.search" @click="resetFilters"
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
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Shaxs</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Kontrakt</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Yo'nalish</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Jami</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">To'langan</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Qolgan qarz</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                        <tr v-if="!debtors.data?.length">
                            <td colspan="7" class="text-center py-16 text-gray-400">
                                <Icon icon="mdi:cash-check" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                                <p class="text-sm">Qarzdor topilmadi</p>
                            </td>
                        </tr>
                        <tr v-for="d in debtors.data ?? []" :key="d.id" class="hover:bg-gray-50 transition-colors">

                            <!-- Shaxs -->
                            <td class="px-4 py-3">
                                <p class="text-sm font-semibold text-gray-900">{{ d.person?.full_name || '—' }}</p>
                                <p class="text-xs text-gray-400">{{ d.person?.phone || '—' }}</p>
                            </td>

                            <!-- Kontrakt -->
                            <td class="px-4 py-3">
                                <span class="text-sm font-mono font-semibold text-[#0f3460]">{{ d.contract_number }}</span>
                                <p class="text-xs text-gray-400 mt-0.5">{{ formatDate(d.signed_at) }}</p>
                            </td>

                            <!-- Yo'nalish -->
                            <td class="px-4 py-3">
                                <p class="text-xs text-gray-700">{{ d.direction?.name_uz || '—' }}</p>
                                <p class="text-xs text-gray-400">{{ d.direction?.faculty || '' }}</p>
                            </td>

                            <!-- Jami -->
                            <td class="px-4 py-3 text-right text-sm text-gray-700">{{ formatAmount(d.amount) }}</td>

                            <!-- To'langan -->
                            <td class="px-4 py-3 text-right text-sm text-green-700">{{ formatAmount(d.paid_amount) }}</td>

                            <!-- Qolgan qarz -->
                            <td class="px-4 py-3 text-right">
                                <span class="text-sm font-bold text-amber-700">{{ formatAmount(d.remaining_amount) }}</span>
                            </td>

                            <!-- Amallar -->
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1.5">
                                    <Link :href="route('admin.contracts.show', d.id)"
                                          class="text-xs font-medium text-[#0f3460] hover:text-[#533483] flex items-center gap-1 whitespace-nowrap">
                                        <Icon icon="mdi:eye-outline" class="w-3.5 h-3.5" />
                                        Kontraktni ko'rish
                                    </Link>
                                    <Link v-if="d.person"
                                          :href="route('admin.crm.contact.show', [d.person.type, d.person.id])"
                                          class="text-xs font-medium text-gray-500 hover:text-gray-700 flex items-center gap-1 whitespace-nowrap">
                                        <Icon icon="mdi:phone-outline" class="w-3.5 h-3.5" />
                                        Muloqot tarixi
                                    </Link>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="(debtors.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500">{{ debtors.from }}–{{ debtors.to }} / {{ debtors.total }}</p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in (debtors.links ?? [])" :key="link.label">
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
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    debtors: { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
    totals:  { type: Object, default: () => ({ count: 0, amount: 0 }) },
    filters: { type: Object, default: () => ({}) },
})

const filters = ref({
    search: props.filters.search || '',
    sort:   props.filters.sort   || 'remaining_desc',
})

const applyFilters = () => {
    router.get(route('admin.crm.debtors.index'), filters.value, {
        preserveState: true, replace: true,
    })
}

let searchTimer = null
const debouncedSearch = () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(applyFilters, 400)
}

const resetFilters = () => {
    filters.value = { search: '', sort: filters.value.sort }
    applyFilters()
}

// Joriy qidiruv/saralash bilan bir xil natijani eksport qilish
const exportUrl = computed(() => {
    const params = new URLSearchParams(
        Object.entries(filters.value).filter(([, v]) => v !== '' && v !== null)
    )
    const query = params.toString()
    return route('admin.crm.debtors.export') + (query ? `?${query}` : '')
})

const formatAmount = (amount) => {
    if (!amount) return '0 so\'m'
    return new Intl.NumberFormat('uz-UZ').format(amount) + " so'm"
}

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric' })
}
</script>

<style scoped>
.btn-secondary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    justify-content: center;
    padding: 0.625rem 1.25rem;
    border-radius: 0.75rem;
    background: white;
    color: #374151;
    font-size: 0.875rem;
    font-weight: 600;
    border: 1.5px solid #e5e7eb;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}
.btn-secondary:hover { background: #f9fafb; }
</style>
