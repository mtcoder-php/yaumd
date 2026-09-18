<template>
    <AppLayout title="Kontraktni tahrirlash">
        <div class="max-w-2xl mx-auto space-y-5">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.contracts.show', contract.id)"
                    class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition"
                >
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Kontraktni tahrirlash</h1>
                    <p class="text-sm text-gray-500 font-mono mt-0.5">{{ contract.contract_number }}</p>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <div class="space-y-5">

                    <!-- Abituriyent / Talaba (o'zgartirilmaydi) -->
                    <div>
                        <label class="field-label">{{ contract.applicant ? 'Abituriyent' : 'Talaba' }}</label>
                        <div class="px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 flex items-center gap-2">
                            <Icon icon="mdi:account-outline" class="w-4 h-4 text-gray-400" />
                            {{ person?.last_name }} {{ person?.first_name }}
                            <span class="font-mono text-gray-400 text-xs ml-1">
                                ({{ person?.passport_series }})
                            </span>
                        </div>
                    </div>

                    <!-- Yo'nalish (o'zgartirilmaydi) -->
                    <div>
                        <label class="field-label">Yo'nalish</label>
                        <div class="px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 flex items-center gap-2">
                            <Icon icon="mdi:school-outline" class="w-4 h-4 text-gray-400" />
                            {{ contract.direction?.name_uz || '—' }}
                        </div>
                    </div>

                    <!-- To'lov turi -->
                    <div>
                        <label class="field-label"><span class="req">*</span> To'lov turi</label>
                        <div class="flex gap-3">
                            <button
                                v-for="pt in paymentTypes"
                                :key="pt.value"
                                type="button"
                                @click="form.payment_type = pt.value"
                                class="flex-1 flex items-center gap-3 px-4 py-3 rounded-xl border-2 transition-all text-left"
                                :class="form.payment_type === pt.value ? 'option-active' : 'option-idle'"
                            >
                                <Icon :icon="pt.icon" class="w-5 h-5"
                                      :class="form.payment_type === pt.value ? 'text-brand-600' : 'text-gray-400'" />
                                <div>
                                    <p class="text-sm font-semibold"
                                       :class="form.payment_type === pt.value ? 'text-brand-600' : 'text-gray-700'">
                                        {{ pt.label }}
                                    </p>
                                    <p class="text-xs text-gray-400">{{ pt.desc }}</p>
                                </div>
                            </button>
                        </div>
                        <p v-if="form.errors.payment_type" class="err">{{ form.errors.payment_type }}</p>
                    </div>

                    <!-- Summa (chegirmasiz, to'liq narx) -->
                    <div>
                        <label class="field-label"><span class="req">*</span> To'liq narx / yillik to'lov (so'm)</label>
                        <div class="relative">
                            <input
                                v-model="form.base_amount"
                                type="number"
                                placeholder="0"
                                min="0"
                                class="field-input pr-16"
                                :class="form.errors.base_amount ? 'field-error' : ''"
                                :disabled="form.payment_type === 'grant'"
                            >
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium">
                                so'm
                            </span>
                        </div>
                        <p v-if="form.errors.base_amount" class="err">{{ form.errors.base_amount }}</p>
                        <p v-if="form.payment_type === 'grant'" class="text-xs text-green-600 mt-1">
                            Grant bo'lganligi uchun summa 0 so'm
                        </p>
                        <p v-else-if="form.base_amount" class="text-xs text-gray-400 mt-1">
                            {{ formatAmount(form.base_amount) }}
                        </p>
                    </div>

                    <!-- Chegirma -->
                    <div v-if="form.payment_type !== 'grant'">
                        <label class="field-label">Chegirma</label>
                        <div class="flex flex-wrap gap-2">
                            <button
                                v-for="p in discountPercents"
                                :key="p"
                                type="button"
                                @click="onDiscountPercentChange(p)"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold border-2 transition-all"
                                :class="Number(form.discount_percent) === p ? 'discount-active' : 'discount-idle'"
                            >
                                {{ p === 0 ? "Yo'q" : `${p}%` }}
                            </button>
                        </div>

                        <div v-if="form.discount_percent > 0" class="mt-3 space-y-3">
                            <div>
                                <label class="field-label"><span class="req">*</span> Chegirma sababi</label>
                                <select v-model="form.discount_reason" class="field-input"
                                        :class="form.errors.discount_reason ? 'field-error' : ''">
                                    <option value="">Tanlang</option>
                                    <option v-for="(label, key) in discountReasons" :key="key" :value="key">
                                        {{ label }}
                                    </option>
                                </select>
                                <p v-if="form.errors.discount_reason" class="err">{{ form.errors.discount_reason }}</p>
                            </div>
                            <div v-if="form.discount_reason === 'other'">
                                <label class="field-label"><span class="req">*</span> Izoh</label>
                                <textarea v-model="form.discount_note" rows="2" class="field-input"
                                          :class="form.errors.discount_note ? 'field-error' : ''"
                                          placeholder="Sababni qisqacha yozing" />
                                <p v-if="form.errors.discount_note" class="err">{{ form.errors.discount_note }}</p>
                            </div>

                            <div class="px-4 py-3 rounded-xl flex items-center justify-between bg-brand-50">
                                <span class="text-xs font-semibold text-gray-500">Yakuniy summa (chegirma bilan)</span>
                                <span class="text-sm font-bold text-brand-600">{{ formatAmount(netAmount) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="field-label"><span class="req">*</span> Status</label>
                        <div class="flex flex-col gap-2">
                            <button
                                v-for="s in statuses"
                                :key="s.value"
                                type="button"
                                @click="form.status = s.value"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl border-2 transition-all text-left"
                                :class="form.status === s.value ? 'option-active' : 'option-idle'"
                            >
                                <Icon :icon="s.icon" class="w-4 h-4"
                                      :class="form.status === s.value ? 'text-brand-600' : 'text-gray-400'" />
                                <span class="text-sm font-semibold"
                                      :class="form.status === s.value ? 'text-brand-600' : 'text-gray-700'">
                                    {{ s.label }}
                                </span>
                                <Icon v-if="form.status === s.value" icon="mdi:check" class="w-4 h-4 ml-auto text-brand-600" />
                            </button>
                        </div>
                        <p v-if="form.errors.status" class="err">{{ form.errors.status }}</p>
                    </div>

                </div>

                <!-- Tugmalar -->
                <div class="flex gap-3 mt-6">
                    <Link :href="route('admin.contracts.show', contract.id)" class="btn-neutral flex-1 justify-center">
                        <Icon icon="mdi:close" class="w-4 h-4" />
                        Bekor qilish
                    </Link>
                    <button type="button" @click="submit" :disabled="form.processing" class="btn-brand flex-1 justify-center">
                        <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                        <Icon v-else icon="mdi:content-save-outline" class="w-4 h-4" />
                        {{ form.processing ? 'Saqlanmoqda...' : 'Saqlash' }}
                    </button>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    contract: { type: Object, required: true },
})

// Kontrakt Abituriyentlar oqimi orqali (applicant) yoki talaba
// to'g'ridan-to'g'ri kiritilganda (student) yaratilgan bo'lishi mumkin
const person = props.contract.applicant ?? props.contract.student

const form = useForm({
    // Eski kontraktlarda base_amount to'ldirilgan (migratsiya paytida
    // amount'dan ko'chirilgan) — shunga qaramay fallback qo'yiladi.
    base_amount:      props.contract.base_amount ?? props.contract.amount ?? 0,
    payment_type:     props.contract.payment_type || 'contract',
    status:           props.contract.status       || 'draft',
    discount_percent: props.contract.discount_percent || 0,
    discount_reason:  props.contract.discount_reason  || '',
    discount_note:    props.contract.discount_note    || '',
})

const discountPercents = [0, 10, 20, 25, 50, 75, 100]
const discountReasons = {
    family:     'Oilaviy sharoit',
    orphan:     'Yetim',
    disability: 'Nogironligi bor',
    low_income: "Kam ta'minlangan",
    other:      'Boshqa',
}

const netAmount = computed(() => {
    const base = Number(form.base_amount) || 0
    const percent = Number(form.discount_percent) || 0
    return Math.round(base * (1 - percent / 100))
})

const onDiscountPercentChange = (p) => {
    form.discount_percent = p
    if (p === 0) {
        form.discount_reason = ''
        form.discount_note = ''
    }
}

const paymentTypes = [
    { value: 'contract', label: 'Kontrakt', desc: "Pullik ta'lim", icon: 'mdi:file-sign' },
    { value: 'grant',    label: 'Grant',    desc: "Bepul ta'lim",  icon: 'mdi:medal-outline' },
]

const statuses = [
    { value: 'draft',     label: 'Qoralama',  icon: 'mdi:file-outline' },
    { value: 'signed',    label: 'Imzolandi', icon: 'mdi:file-sign' },
    { value: 'paid',      label: "To'landi",  icon: 'mdi:check-circle-outline' },
    { value: 'cancelled', label: 'Bekor',     icon: 'mdi:close-circle-outline' },
]

const submit = () => {
    if (form.payment_type === 'grant') {
        form.base_amount = 0
        form.discount_percent = 0
        form.discount_reason = ''
        form.discount_note = ''
    }
    form.put(route('admin.contracts.update', props.contract.id))
}

const formatAmount = (amount) => {
    if (!amount) return ''
    return new Intl.NumberFormat('uz-UZ').format(amount) + ' so\'m'
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
}
.field-input:focus { border-color: var(--color-brand-600); background: white; }
.field-input:disabled { opacity: 0.6; cursor: not-allowed; }
.field-error { border-color: #f87171 !important; background: #fef2f2 !important; }
.err { color: #ef4444; font-size: 0.7rem; margin-top: 0.25rem; display: block; }

.option-idle { border-color: #e5e7eb; background: #fafafa; }
.option-active { border-color: var(--color-brand-600); background: var(--color-brand-50); }

.discount-idle { border-color: #e5e7eb; background: #fafafa; color: #6b7280; }
.discount-active { border-color: var(--color-brand-600); background: var(--color-brand-50); color: var(--color-brand-600); }
</style>
