<template>
    <AppLayout title="Qarzdorlar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Qarzdorlar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Kontrakt bo'yicha qarzi qolgan abituriyent/talabalar</p>
                </div>
                <a :href="exportUrl" class="btn-neutral">
                    <Icon icon="mdi:file-download-outline" class="w-4 h-4" />
                    Excel'ga eksport
                </a>
            </div>

            <!-- Stat kartalar -->
            <div class="grid grid-cols-2 gap-4 max-w-md">
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1 bg-brand-600" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">Qarzdorlar soni</p>
                        <p class="text-2xl font-bold text-gray-900">{{ totals.count }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1" style="background:#f59e0b" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">Jami qarz summasi</p>
                        <p class="text-2xl font-bold text-gray-900">{{ formatAmount(totals.amount) }}</p>
                    </div>
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
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-brand-600 bg-gray-50"
                        @input="debouncedSearch"
                    >
                </div>

                <select v-model="filters.sort" class="select-filter" @change="applyFilters">
                    <option value="remaining_desc">Qarz: ko'pdan kamga</option>
                    <option value="remaining_asc">Qarz: kamdan ko'pga</option>
                    <option value="signed_at_desc">Sana: yangidan eskiga</option>
                    <option value="signed_at_asc">Sana: eskidan yangiga</option>
                </select>

                <button v-if="filters.search" @click="resetFilters" class="btn-neutral">
                    <Icon icon="mdi:close" class="w-4 h-4" />
                    Tozalash
                </button>
            </div>

            <!-- Table -->
            <div class="table-grid-wrap">
                <table class="table-grid">
                    <thead>
                    <tr>
                        <th>Shaxs</th>
                        <th>Kontrakt</th>
                        <th>Yo'nalish</th>
                        <th class="text-right">Jami</th>
                        <th class="text-right">To'langan</th>
                        <th class="text-right">Qolgan qarz</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="!debtors.data?.length">
                        <td colspan="7" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:cash-check" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Qarzdor topilmadi</p>
                        </td>
                    </tr>
                    <tr v-for="d in debtors.data ?? []" :key="d.id">

                        <!-- Shaxs -->
                        <td>
                            <p class="text-sm font-semibold text-gray-900">{{ d.person?.full_name || '—' }}</p>
                            <p class="text-xs text-gray-400">{{ d.person?.phone || '—' }}</p>
                        </td>

                        <!-- Kontrakt -->
                        <td>
                            <span class="text-sm font-mono font-semibold text-brand-600">{{ d.contract_number }}</span>
                            <p class="text-xs text-gray-400 mt-0.5">{{ formatDate(d.signed_at) }}</p>
                        </td>

                        <!-- Yo'nalish -->
                        <td>
                            <p class="text-xs text-gray-700">{{ d.direction?.name_uz || '—' }}</p>
                            <p class="text-xs text-gray-400">{{ d.direction?.faculty || '' }}</p>
                        </td>

                        <!-- Jami -->
                        <td class="text-right text-sm text-gray-700">{{ formatAmount(d.amount) }}</td>

                        <!-- To'langan -->
                        <td class="text-right text-sm text-green-700">{{ formatAmount(d.paid_amount) }}</td>

                        <!-- Qolgan qarz -->
                        <td class="text-right">
                            <span class="badge-pill badge-warning">{{ formatAmount(d.remaining_amount) }}</span>
                        </td>

                        <!-- Amallar -->
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="route('admin.contracts.show', d.id)" title="Kontraktni ko'rish" class="btn-ghost-icon">
                                    <Icon icon="mdi:eye-outline" class="w-4 h-4" />
                                </Link>
                                <Link v-if="d.person"
                                      :href="route('admin.crm.contact.show', [d.person.type, d.person.id])"
                                      title="Muloqot tarixi" class="btn-ghost-icon">
                                    <Icon icon="mdi:phone-outline" class="w-4 h-4" />
                                </Link>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="(debtors.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-3">
                    <p class="text-xs text-gray-500">{{ debtors.from }}–{{ debtors.to }} / {{ debtors.total }}</p>
                    <div class="flex items-center gap-1.5">
                        <template v-for="link in (debtors.links ?? [])" :key="link.label">
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

// Laravel'ning standart pagination yorliqlari o'rniga sof strelka
// ikonkalarini ko'rsatish uchun (Foydalanuvchilar sahifasidagi bilan
// bir xil naqsh).
const isPrevLabel = (label) => /Previous|&laquo;|«/i.test(label)
const isNextLabel = (label) => /Next|&raquo;|»/i.test(label)
</script>
