<template>
    <AppLayout title="To'lovlar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">To'lovlar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Jami: {{ payments.total }} ta to'lov</p>
                </div>
                <button @click="openAddModal" class="btn-brand">
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    To'lov qabul qilish
                </button>
            </div>

            <!-- Stat kartalar -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1" style="background:#22c55e" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">Jami tushum</p>
                        <p class="text-lg font-bold text-gray-900">{{ formatAmount(stats.total) }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1 bg-brand-600" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">Bugungi tushum</p>
                        <p class="text-lg font-bold text-gray-900">{{ formatAmount(stats.today) }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1" style="background:#9ca3af" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">Qabul qilingan</p>
                        <p class="text-lg font-bold text-gray-900">{{ stats.count }} ta</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1" style="background:#f59e0b" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">Kutilayotgan</p>
                        <p class="text-lg font-bold text-gray-900">{{ stats.pending }} ta</p>
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
                        placeholder="Ism, pasport, tranzaksiya ID..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-brand-600 bg-gray-50"
                        @input="debouncedSearch"
                    >
                </div>

                <select v-model="filters.status" class="select-filter" @change="applyFilters">
                    <option value="">Barcha statuslar</option>
                    <option value="pending">Kutilmoqda</option>
                    <option value="paid">To'landi</option>
                    <option value="failed">Xato</option>
                    <option value="refunded">Qaytarildi</option>
                </select>

                <select v-model="filters.provider" class="select-filter" @change="applyFilters">
                    <option value="">Barcha turlar</option>
                    <option value="cash">Naqd</option>
                    <option value="click">Click</option>
                    <option value="payme">Payme</option>
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
                        <th>Abituriyent</th>
                        <th>Kontrakt</th>
                        <th>Summa</th>
                        <th>To'lov turi</th>
                        <th>Status</th>
                        <th>Sana</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="!payments.data?.length">
                        <td colspan="7" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:cash-off" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">To'lovlar topilmadi</p>
                        </td>
                    </tr>
                    <tr v-for="p in payments.data ?? []" :key="p.id">

                        <td>
                            <p class="text-sm font-medium text-gray-900">
                                {{ personOf(p)?.last_name }} {{ personOf(p)?.first_name }}
                            </p>
                            <p class="text-xs text-gray-400 font-mono">{{ personOf(p)?.passport_series }}</p>
                        </td>

                        <td>
                            <Link :href="route('admin.contracts.show', p.contract_id)"
                                  class="text-xs font-mono font-semibold text-brand-600 hover:underline">
                                {{ p.contract?.contract_number }}
                            </Link>
                        </td>

                        <td>
                            <span class="text-sm font-bold text-gray-800">{{ formatAmount(p.amount) }}</span>
                        </td>

                        <td>
                            <span class="badge-pill" :class="providerBadge(p.provider)">
                                <Icon :icon="providerIcon(p.provider)" class="w-3 h-3" />
                                {{ providerLabel(p.provider) }}
                            </span>
                        </td>

                        <td>
                            <span class="badge-pill" :class="statusBadge(p.status)">
                                {{ statusLabel(p.status) }}
                            </span>
                        </td>

                        <td class="text-xs text-gray-400">
                            {{ formatDate(p.paid_at || p.created_at) }}
                        </td>

                        <td>
                            <div class="flex justify-end">
                                <button @click="confirmDelete(p)" title="O'chirish" class="btn-ghost-icon danger">
                                    <Icon icon="mdi:delete-outline" class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="(payments.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-3">
                    <p class="text-xs text-gray-500">{{ payments.from }}–{{ payments.to }} / {{ payments.total }}</p>
                    <div class="flex items-center gap-1.5">
                        <template v-for="link in (payments.links ?? [])" :key="link.label">
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

        <!-- To'lov qabul qilish modal -->
        <div v-if="addModal" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="closeAddModal">
            <div class="bg-white rounded-2xl w-full max-w-md p-6">

                <!-- Onlayn to'lov: havola/QR va uning holati -->
                <template v-if="onlineCheckout">
                    <h3 class="text-base font-bold text-gray-900 mb-5 flex items-center gap-2">
                        <Icon :icon="providerIcon(payForm.provider)" class="w-5 h-5 text-brand-600" />
                        {{ providerLabel(payForm.provider) }} orqali to'lov
                    </h3>

                    <div class="flex flex-col items-center text-center">
                        <template v-if="onlineCheckout.status === 'pending'">
                            <img :src="onlineCheckout.qrDataUrl" alt="QR kod"
                                 class="w-44 h-44 rounded-xl border border-gray-100 mb-4">
                            <p class="text-xs text-gray-500 mb-3">
                                To'lovchi shu QR kodni skaner qilsin yoki quyidagi havolani oching/yuboring
                            </p>
                            <div class="w-full flex items-center gap-2 mb-4">
                                <input :value="onlineCheckout.checkoutUrl" readonly
                                       class="field-input flex-1 text-xs font-mono truncate"
                                       @focus="$event.target.select()">
                                <button @click="copyLink" type="button" class="btn-neutral flex-shrink-0">
                                    {{ linkCopied ? 'Nusxalandi' : 'Nusxalash' }}
                                </button>
                            </div>
                            <div class="flex items-center gap-2 text-xs font-medium text-amber-600">
                                <Icon icon="mdi:loading" class="w-4 h-4 animate-spin" />
                                To'lov kutilmoqda...
                            </div>
                        </template>

                        <template v-else-if="onlineCheckout.status === 'paid'">
                            <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mb-3">
                                <Icon icon="mdi:check" class="w-8 h-8 text-green-600" />
                            </div>
                            <p class="text-sm font-semibold text-gray-800">To'lov muvaffaqiyatli qabul qilindi!</p>
                        </template>

                        <template v-else>
                            <div class="w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mb-3">
                                <Icon icon="mdi:close" class="w-8 h-8 text-red-500" />
                            </div>
                            <p class="text-sm font-semibold text-gray-800">To'lov amalga oshmadi yoki bekor qilindi</p>
                        </template>
                    </div>

                    <button @click="closeAddModal" class="btn-neutral w-full justify-center mt-6">Yopish</button>
                </template>

                <!-- Kontrakt/summa/to'lov turini tanlash -->
                <template v-else>
                    <h3 class="text-base font-bold text-gray-900 mb-5 flex items-center gap-2">
                        <Icon icon="mdi:cash-plus" class="w-5 h-5 text-brand-600" />
                        To'lov qabul qilish
                    </h3>

                    <div class="space-y-4">

                        <!-- Kontrakt tanlash -->
                        <div>
                            <label class="field-label"><span class="req">*</span> Kontrakt</label>
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
                            <label class="field-label"><span class="req">*</span> Summa</label>
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
                            <label class="field-label"><span class="req">*</span> To'lov turi</label>
                            <div class="flex gap-2">
                                <button v-for="pv in providers" :key="pv.value" type="button"
                                        @click="payForm.provider = pv.value"
                                        class="flex-1 flex flex-col items-center gap-1 py-3 rounded-xl border-2 cursor-pointer transition-all"
                                        :class="payForm.provider === pv.value ? 'option-active' : 'option-idle'">
                                    <Icon :icon="pv.icon" class="w-5 h-5"
                                          :class="payForm.provider === pv.value ? 'text-brand-600' : 'text-gray-400'" />
                                    <span class="text-xs font-semibold"
                                          :class="payForm.provider === pv.value ? 'text-brand-600' : 'text-gray-700'">
                                        {{ pv.label }}
                                    </span>
                                </button>
                            </div>
                            <p v-if="payForm.provider !== 'cash'" class="text-xs text-gray-400 mt-1.5">
                                Havola/QR generatsiya qilinadi — to'lovchi o'zi to'laydi, tizim avtomatik tasdiqlaydi.
                            </p>
                            <p v-if="payErrors.provider" class="err">{{ payErrors.provider }}</p>
                            <p v-if="onlineError" class="err">{{ onlineError }}</p>
                        </div>

                    </div>

                    <div class="flex gap-3 mt-6">
                        <button @click="closeAddModal" class="btn-neutral flex-1 justify-center">Bekor qilish</button>
                        <button v-if="payForm.provider === 'cash'" @click="submitPayment" :disabled="paying" class="btn-brand flex-1 justify-center">
                            <Icon v-if="paying" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                            <Icon v-else icon="mdi:check" class="w-4 h-4" />
                            {{ paying ? 'Saqlanmoqda...' : 'Qabul qilish' }}
                        </button>
                        <button v-else @click="startOnlineCheckout" :disabled="creatingLink" class="btn-brand flex-1 justify-center">
                            <Icon v-if="creatingLink" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                            <Icon v-else icon="mdi:qrcode" class="w-4 h-4" />
                            {{ creatingLink ? 'Yaratilmoqda...' : 'Havola/QR yaratish' }}
                        </button>
                    </div>
                </template>
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
                    <button @click="deleteTarget = null" class="btn-neutral flex-1 justify-center">Bekor qilish</button>
                    <button @click="submitDelete" class="btn-danger-pill flex-1">O'chirish</button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref, computed, nextTick, onBeforeUnmount } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'
import AppLayout from '@/Layouts/AppLayout.vue'
import IMask from 'imask'
import QRCode from 'qrcode'

const props = defineProps({
    payments:        { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
    filters:         { type: Object, default: () => ({}) },
    stats:           { type: Object, default: () => ({}) },
    activeContracts: { type: Array,  default: () => [] },
})

// To'lov kontrakti Abituriyentlar oqimi orqali (applicant) yoki talaba
// to'g'ridan-to'g'ri kiritilganda (student) yaratilgan bo'lishi mumkin —
// Admin/Contracts/Index.vue'dagi bilan bir xil yondashuv.
const personOf = (p) => p.contract?.applicant ?? p.contract?.student

const addModal     = ref(false)
const paying       = ref(false)
const deleteTarget = ref(null)
const payErrors    = ref({})
const amountRef    = ref(null)
let   amountMask   = null

// Click/Payme uchun HAQIQIY onlayn to'lov havolasi (QR) oqimi — kassir
// kontrakt/summani tanlagach havola generatsiya qiladi, to'lovchi o'z
// qurilmasida to'laydi, tizim server-serverga keladigan callback orqali
// avtomatik tasdiqlaydi (admin.payments.online / admin.payments.status).
const creatingLink   = ref(false)
const onlineCheckout = ref(null) // { paymentId, checkoutUrl, qrDataUrl, status }
const onlineError    = ref('')
const linkCopied     = ref(false)
let   pollTimer      = null

const payForm = ref({
    contract_id: '',
    amount:      '',
    provider:    'cash',
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
    payForm.value   = { contract_id: '', amount: '', provider: 'cash' }
    payErrors.value = {}
    onlineCheckout.value = null
    onlineError.value    = ''
    stopPolling()
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

const closeAddModal = () => {
    addModal.value = false
    onlineCheckout.value = null
    onlineError.value    = ''
    stopPolling()
    if (amountMask) { amountMask.destroy(); amountMask = null }
}

const stopPolling = () => {
    if (pollTimer) { clearInterval(pollTimer); pollTimer = null }
}
onBeforeUnmount(stopPolling)

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
            paying.value = false
            closeAddModal()
        },
        onError: (errors) => {
            payErrors.value = errors
            paying.value    = false
        },
    })
}

// Click/Payme: haqiqiy to'lov havolasini generatsiya qilish (JSON so'rov —
// Inertia visit emas, chunki bu admin panelining o'zi emas, to'lovchining
// QR/havolasi kerak). Muvaffaqiyatli bo'lsa, natija QR'ga aylantiriladi va
// to'lov holati bir necha soniyada bir marta so'raladi (pollingda).
const startOnlineCheckout = async () => {
    payErrors.value  = {}
    onlineError.value = ''
    if (!payForm.value.contract_id) { payErrors.value.contract_id = 'Kontraktni tanlang'; return }
    if (!payForm.value.amount)      { payErrors.value.amount = 'Summani kiriting'; return }

    creatingLink.value = true
    try {
        const { data } = await window.axios.post(route('admin.payments.online', payForm.value.provider), {
            contract_id: payForm.value.contract_id,
            amount:      payForm.value.amount,
        })

        const qrDataUrl = await QRCode.toDataURL(data.checkout_url, { width: 220, margin: 1 })

        onlineCheckout.value = {
            paymentId:   data.payment_id,
            checkoutUrl: data.checkout_url,
            qrDataUrl,
            status:      'pending',
        }

        stopPolling()
        pollTimer = setInterval(checkOnlineStatus, 3000)
    } catch (e) {
        if (e.response?.status === 422 && e.response?.data?.errors) {
            payErrors.value = e.response.data.errors
        } else {
            onlineError.value = e.response?.data?.message || "Havola yaratishda xatolik yuz berdi"
        }
    } finally {
        creatingLink.value = false
    }
}

const checkOnlineStatus = async () => {
    if (!onlineCheckout.value) { stopPolling(); return }

    try {
        const { data } = await window.axios.get(route('admin.payments.status', onlineCheckout.value.paymentId))
        if (data.status === onlineCheckout.value.status) return

        onlineCheckout.value.status = data.status

        if (data.status === 'paid') {
            stopPolling()
            router.reload({ only: ['payments', 'stats'] })
            setTimeout(closeAddModal, 1800)
        } else if (data.status === 'failed' || data.status === 'cancelled') {
            stopPolling()
        }
    } catch (e) {
        // Vaqtinchalik tarmoq xatosi — keyingi urinishda qayta tekshiriladi.
    }
}

const copyLink = async () => {
    if (!onlineCheckout.value) return
    try {
        await navigator.clipboard.writeText(onlineCheckout.value.checkoutUrl)
        linkCopied.value = true
        setTimeout(() => { linkCopied.value = false }, 1500)
    } catch (e) {
        // Clipboard mavjud emas — havola input orqali qo'lda nusxalanadi.
    }
}

const confirmDelete = (p) => { deleteTarget.value = p }
const submitDelete = () => {
    router.delete(route('admin.payments.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null },
    })
}

const providers = [
    { value: 'cash',  label: 'Naqd',  icon: 'mdi:cash' },
    { value: 'click', label: 'Click', icon: 'mdi:cellphone' },
    { value: 'payme', label: 'Payme', icon: 'mdi:credit-card-outline' },
]

const providerLabel = (p) => providers.find(x => x.value === p)?.label || p
const providerIcon  = (p) => providers.find(x => x.value === p)?.icon  || 'mdi:cash'
const providerBadge = (p) => ({
    cash:  'badge-success',
    click: 'badge-brand',
    payme: 'badge-warning',
}[p] || 'badge-neutral')

const statuses = [
    { value: 'pending',  label: 'Kutilmoqda', class: 'badge-warning' },
    { value: 'paid',     label: "To'landi",   class: 'badge-success' },
    { value: 'failed',   label: 'Xato',       class: 'badge-danger' },
    { value: 'refunded', label: 'Qaytarildi', class: 'badge-neutral' },
]

const statusLabel = (s) => statuses.find(x => x.value === s)?.label || s
const statusBadge = (s) => statuses.find(x => x.value === s)?.class || 'badge-neutral'

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

// Laravel'ning standart pagination yorliqlari o'rniga sof strelka
// ikonkalarini ko'rsatish uchun (Foydalanuvchilar sahifasidagi bilan
// bir xil naqsh).
const isPrevLabel = (label) => /Previous|&laquo;|«/i.test(label)
const isNextLabel = (label) => /Next|&raquo;|»/i.test(label)
</script>

<style scoped>
.field-label { display: block; font-size: 0.78rem; font-weight: 600; color: #374151; margin-bottom: 0.375rem; }
.req { color: #ef4444; margin-right: 0.15rem; }
.field-input { width: 100%; padding: 0.6rem 0.875rem; border-radius: 0.625rem; border: 1.5px solid #e5e7eb; font-size: 0.875rem; color: #111827; background: #fafafa; outline: none; transition: border-color 0.2s; appearance: auto; }
.field-input:focus { border-color: var(--color-brand-600); background: white; }
.field-error { border-color: #f87171 !important; background: #fef2f2 !important; }
.err { color: #ef4444; font-size: 0.7rem; margin-top: 0.25rem; display: block; }

.option-idle { border-color: #e5e7eb; background: #fafafa; }
.option-active { border-color: var(--color-brand-600); background: var(--color-brand-50); }
</style>
