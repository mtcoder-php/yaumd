<template>
    <AppLayout title="To'lovlar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">To'lovlar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Jami: {{ payments.total }} ta to'lov</p>
                </div>
                <button @click="openAddModal" class="btn-primary">
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    To'lov qabul qilish
                </button>
            </div>

            <!-- Stat kartalar -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                <div class="bg-white rounded-xl border border-gray-100 p-4"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <p class="text-xs text-gray-400 mb-1">Jami tushum</p>
                    <p class="text-lg font-bold text-green-600">{{ formatAmount(stats.total) }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <p class="text-xs text-gray-400 mb-1">Bugungi tushum</p>
                    <p class="text-lg font-bold text-[#0f3460]">{{ formatAmount(stats.today) }}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <p class="text-xs text-gray-400 mb-1">Qabul qilingan</p>
                    <p class="text-lg font-bold text-gray-800">{{ stats.count }} ta</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <p class="text-xs text-gray-400 mb-1">Kutilayotgan</p>
                    <p class="text-lg font-bold text-amber-600">{{ stats.pending }} ta</p>
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
                        placeholder="Ism, pasport, tranzaksiya ID..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50"
                        @input="debouncedSearch"
                    >
                </div>

                <select v-model="filters.status"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50"
                        @change="applyFilters">
                    <option value="">Barcha statuslar</option>
                    <option value="pending">Kutilmoqda</option>
                    <option value="paid">To'landi</option>
                    <option value="failed">Xato</option>
                    <option value="refunded">Qaytarildi</option>
                </select>

                <select v-model="filters.provider"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50"
                        @change="applyFilters">
                    <option value="">Barcha turlar</option>
                    <option value="cash">Naqd</option>
                    <option value="click">Click</option>
                    <option value="payme">Payme</option>
                </select>

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
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Abituriyent</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Kontrakt</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Summa</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">To'lov turi</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Sana</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                        <tr v-if="!payments.data?.length">
                            <td colspan="7" class="text-center py-16 text-gray-400">
                                <Icon icon="mdi:cash-off" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                                <p class="text-sm">To'lovlar topilmadi</p>
                            </td>
                        </tr>
                        <tr v-for="p in payments.data ?? []" :key="p.id"
                            class="hover:bg-gray-50 transition-colors">

                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ p.contract?.applicant?.last_name }} {{ p.contract?.applicant?.first_name }}
                                </p>
                                <p class="text-xs text-gray-400 font-mono">{{ p.contract?.applicant?.passport_series }}</p>
                            </td>

                            <td class="px-4 py-3">
                                <Link :href="route('admin.contracts.show', p.contract_id)"
                                      class="text-xs font-mono font-semibold text-[#0f3460] hover:underline">
                                    {{ p.contract?.contract_number }}
                                </Link>
                            </td>

                            <td class="px-4 py-3">
                                <span class="text-sm font-bold text-gray-800">{{ formatAmount(p.amount) }}</span>
                            </td>

                            <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                          :class="providerBadge(p.provider)">
                                        <Icon :icon="providerIcon(p.provider)" class="w-3 h-3" />
                                        {{ providerLabel(p.provider) }}
                                    </span>
                            </td>

                            <td class="px-4 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                          :class="statusBadge(p.status)">
                                        {{ statusLabel(p.status) }}
                                    </span>
                            </td>

                            <td class="px-4 py-3 text-xs text-gray-400">
                                {{ formatDate(p.paid_at || p.created_at) }}
                            </td>

                            <td class="px-4 py-3">
                                <button @click="confirmDelete(p)"
                                        class="text-xs font-medium text-red-500 hover:text-red-700 flex items-center gap-1">
                                    <Icon icon="mdi:delete-outline" class="w-3.5 h-3.5" />
                                    O'chirish
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="(payments.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500">{{ payments.from }}–{{ payments.to }} / {{ payments.total }}</p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in (payments.links ?? [])" :key="link.label">
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

        <!-- To'lov qabul qilish modal -->
        <div v-if="addModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="addModal = false">
            <div class="bg-white rounded-2xl w-full max-w-md p-6">
                <h3 class="text-base font-bold text-gray-900 mb-5 flex items-center gap-2">
                    <Icon icon="mdi:cash-plus" class="w-5 h-5 text-[#0f3460]" />
                    To'lov qabul qilish
                </h3>

                <div class="space-y-4">

                    <!-- Kontrakt tanlash -->
                    <div>
                        <label class="field-label">Kontrakt <span class="req">*</span></label>
                        <select v-model="payForm.contract_id" class="field-input">
                            <option value="">Tanlang</option>
                            <option v-for="c in activeContracts" :key="c.id" :value="c.id">
                                {{ c.contract_number }} — {{ c.applicant_name }}
                            </option>
                        </select>
                        <p v-if="payErrors.contract_id" class="err">{{ payErrors.contract_id }}</p>
                    </div>

                    <!-- Summa -->
                    <div>
                        <label class="field-label">Summa <span class="req">*</span></label>
                        <div class="relative">
                            <input
                                ref="amountRef"
                                type="text"
                                placeholder="0"
                                class="field-input pr-12"
                                :class="payErrors.amount ? 'field-error' : ''"
                            >
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium">so'm</span>
                        </div>
                        <p v-if="payErrors.amount" class="err">{{ payErrors.amount }}</p>
                    </div>

                    <!-- To'lov turi -->
                    <div>
                        <label class="field-label">To'lov turi <span class="req">*</span></label>
                        <div class="flex gap-2">
                            <button v-for="pv in providers" :key="pv.value" type="button"
                                    @click="!pv.disabled && (payForm.provider = pv.value)"
                                    class="flex-1 flex flex-col items-center gap-1 py-3 rounded-xl border-2 transition-all"
                                    :class="pv.disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'"
                                    :style="!pv.disabled && payForm.provider === pv.value
                ? 'border-color:#0f3460; background:linear-gradient(135deg,#eff6ff,#f5f3ff)'
                : 'border-color:#e5e7eb; background:#fafafa'">
                                <Icon :icon="pv.disabled ? 'mdi:lock-outline' : pv.icon" class="w-5 h-5"
                                      :style="!pv.disabled && payForm.provider === pv.value ? 'color:#0f3460' : 'color:#9ca3af'" />
                                <span class="text-xs font-semibold"
                                      :style="!pv.disabled && payForm.provider === pv.value ? 'color:#0f3460' : 'color:#374151'">
                {{ pv.label }}
            </span>
                                <span v-if="pv.disabled" class="text-xs text-gray-400">tez orada</span>
                            </button>
                        </div>
                        <p v-if="payErrors.provider" class="err">{{ payErrors.provider }}</p>
                    </div>

                    <!-- Tranzaksiya ID (ixtiyoriy) -->
                    <div v-if="payForm.provider !== 'cash'">
                        <label class="field-label">Tranzaksiya ID</label>
                        <input v-model="payForm.transaction_id" type="text" placeholder="Ixtiyoriy"
                               class="field-input">
                    </div>

                </div>

                <div class="flex gap-3 mt-6">
                    <button @click="addModal = false" class="btn-secondary flex-1">Bekor qilish</button>
                    <button @click="submitPayment" :disabled="paying" class="btn-primary flex-1">
                        <Icon v-if="paying" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                        <Icon v-else icon="mdi:check" class="w-4 h-4" />
                        {{ paying ? 'Saqlanmoqda...' : 'Qabul qilish' }}
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
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">To'lovni o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    {{ formatAmount(deleteTarget?.amount) }} miqdoridagi to'lovni o'chirasizmi?
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
import { ref, computed, nextTick } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import IMask from 'imask'

const props = defineProps({
    payments:        { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
    filters:         { type: Object, default: () => ({}) },
    stats:           { type: Object, default: () => ({}) },
    activeContracts: { type: Array,  default: () => [] },
})

const addModal     = ref(false)
const paying       = ref(false)
const deleteTarget = ref(null)
const payErrors    = ref({})
const amountRef    = ref(null)
let   amountMask   = null

const payForm = ref({
    contract_id:    '',
    amount:         '',
    provider:       'cash',
    transaction_id: '',
})

const filters = ref({
    search:   props.filters.search   || '',
    status:   props.filters.status   || '',
    provider: props.filters.provider || '',
})

const hasFilters = computed(() =>
    filters.value.search || filters.value.status || filters.value.provider
)

const openAddModal = () => {
    payForm.value  = { contract_id: '', amount: '', provider: 'cash', transaction_id: '' }
    payErrors.value = {}
    addModal.value  = true

    nextTick(() => {
        if (amountRef.value) {
            amountMask = IMask(amountRef.value, {
                mask: Number,
                thousandsSeparator: '.',
                radix: ',',
                min: 0,
                max: 9999999999,
            })
            amountMask.on('accept', () => {
                payForm.value.amount = amountMask.unmaskedValue
            })
        }
    })
}

const applyFilters = () => {
    router.get(route('admin.payments.index'), filters.value, {
        preserveState: true, replace: true,
    })
}

let searchTimer = null
const debouncedSearch = () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(applyFilters, 400)
}

const resetFilters = () => {
    filters.value = { search: '', status: '', provider: '' }
    applyFilters()
}

const submitPayment = () => {
    payErrors.value = {}
    if (!payForm.value.contract_id) { payErrors.value.contract_id = 'Kontraktni tanlang'; return }
    if (!payForm.value.amount)      { payErrors.value.amount = 'Summani kiriting'; return }
    if (!payForm.value.provider)    { payErrors.value.provider = "To'lov turini tanlang"; return }

    paying.value = true
    router.post(route('admin.payments.store'), payForm.value, {
        onSuccess: () => {
            addModal.value = false
            paying.value   = false
            payForm.value  = { contract_id: '', amount: '', provider: 'cash', transaction_id: '' }
            if (amountMask) { amountMask.destroy(); amountMask = null }
        },
        onError: (errors) => {
            payErrors.value = errors
            paying.value    = false
        },
    })
}

const confirmDelete = (p) => { deleteTarget.value = p }
const submitDelete = () => {
    router.delete(route('admin.payments.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null },
    })
}

const providers = [
    { value: 'cash',  label: 'Naqd',  icon: 'mdi:cash',                disabled: false },
    { value: 'click', label: 'Click', icon: 'mdi:cellphone',            disabled: true  },
    { value: 'payme', label: 'Payme', icon: 'mdi:credit-card-outline',  disabled: true  },
]

const providerLabel = (p) => providers.find(x => x.value === p)?.label || p
const providerIcon  = (p) => providers.find(x => x.value === p)?.icon  || 'mdi:cash'
const providerBadge = (p) => ({
    cash:  'bg-green-50 text-green-700',
    click: 'bg-blue-50 text-blue-700',
    payme: 'bg-purple-50 text-purple-700',
}[p] || 'bg-gray-100 text-gray-600')

const statuses = [
    { value: 'pending',  label: 'Kutilmoqda', class: 'bg-yellow-50 text-yellow-700' },
    { value: 'paid',     label: "To'landi",   class: 'bg-green-50 text-green-700' },
    { value: 'failed',   label: 'Xato',       class: 'bg-red-50 text-red-700' },
    { value: 'refunded', label: 'Qaytarildi', class: 'bg-gray-100 text-gray-600' },
]

const statusLabel = (s) => statuses.find(x => x.value === s)?.label || s
const statusBadge = (s) => statuses.find(x => x.value === s)?.class || 'bg-gray-100 text-gray-600'

const formatAmount = (amount) => {
    if (!amount) return '0 so\'m'
    return new Intl.NumberFormat('uz-UZ').format(amount) + ' so\'m'
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
.field-label { display: block; font-size: 0.78rem; font-weight: 600; color: #374151; margin-bottom: 0.375rem; }
.req { color: #ef4444; }
.field-input { width: 100%; padding: 0.6rem 0.875rem; border-radius: 0.625rem; border: 1.5px solid #e5e7eb; font-size: 0.875rem; color: #111827; background: #fafafa; outline: none; transition: border-color 0.2s; appearance: auto; }
.field-input:focus { border-color: #0f3460; background: white; }
.field-error { border-color: #f87171 !important; background: #fef2f2 !important; }
.err { color: #ef4444; font-size: 0.7rem; margin-top: 0.25rem; display: block; }
.btn-primary { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.75rem; background: linear-gradient(135deg, #0f3460, #533483); color: white; font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer; text-decoration: none; transition: all 0.2s; }
.btn-primary:hover { box-shadow: 0 6px 20px rgba(15,52,96,0.3); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-secondary { display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.75rem; background: white; color: #374151; font-size: 0.875rem; font-weight: 600; border: 1.5px solid #e5e7eb; cursor: pointer; transition: all 0.2s; }
.btn-secondary:hover { background: #f9fafb; }
.btn-danger { display: inline-flex; align-items: center; justify-content: center; padding: 0.625rem 1.25rem; border-radius: 0.75rem; background: #ef4444; color: white; font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer; }
.btn-danger:hover { background: #dc2626; }
</style>
