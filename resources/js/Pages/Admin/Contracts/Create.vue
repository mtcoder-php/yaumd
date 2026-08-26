<template>
    <AppLayout title="Yangi kontrakt">
        <div class="max-w-2xl mx-auto space-y-5">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.contracts.index')"
                    class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition"
                >
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <h1 class="text-xl font-bold text-gray-900">Yangi kontrakt yaratish</h1>
            </div>

            <!-- Form -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <div class="space-y-5">

                    <!-- Abituriyent tanlash -->
                    <div>
                        <label class="field-label">Abituriyent <span class="req">*</span></label>
                        <select
                            v-model="form.applicant_id"
                            class="field-input"
                            :class="form.errors.applicant_id ? 'field-error' : ''"
                            @change="onApplicantChange"
                        >
                            <option value="">Tanlang</option>
                            <option v-for="a in applicants" :key="a.id" :value="a.id">
                                {{ a.name }} — {{ a.passport }}
                            </option>
                        </select>
                        <p v-if="form.errors.applicant_id" class="err">{{ form.errors.applicant_id }}</p>
                    </div>

                    <!-- Yo'nalish (avtomatik) -->
                    <div v-if="selectedApplicant">
                        <label class="field-label">Yo'nalish</label>
                        <div class="px-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700">
                            {{ selectedApplicant.direction || '—' }}
                        </div>
                    </div>

                    <!-- To'lov turi -->
                    <div>
                        <label class="field-label">To'lov turi <span class="req">*</span></label>
                        <div class="flex gap-3">
                            <button
                                v-for="pt in paymentTypes"
                                :key="pt.value"
                                type="button"
                                @click="form.payment_type = pt.value"
                                class="flex-1 flex items-center gap-3 px-4 py-3 rounded-xl border-2 transition-all text-left"
                                :style="form.payment_type === pt.value
                                    ? 'border-color:#0f3460; background:linear-gradient(135deg,#eff6ff,#f5f3ff)'
                                    : 'border-color:#e5e7eb; background:#fafafa'"
                            >
                                <Icon :icon="pt.icon" class="w-5 h-5"
                                      :style="form.payment_type === pt.value ? 'color:#0f3460' : 'color:#9ca3af'" />
                                <div>
                                    <p class="text-sm font-semibold"
                                       :style="form.payment_type === pt.value ? 'color:#0f3460' : 'color:#374151'">
                                        {{ pt.label }}
                                    </p>
                                    <p class="text-xs text-gray-400">{{ pt.desc }}</p>
                                </div>
                            </button>
                        </div>
                        <p v-if="form.errors.payment_type" class="err">{{ form.errors.payment_type }}</p>
                    </div>

                    <!-- Summa -->
                    <div>
                        <label class="field-label">
                            Kontrakt summasi (so'm)
                            <span class="req">*</span>
                        </label>
                        <div class="relative">
                            <input
                                v-model="form.amount"
                                type="number"
                                placeholder="0"
                                min="0"
                                class="field-input pr-16"
                                :class="form.errors.amount ? 'field-error' : ''"
                                :disabled="form.payment_type === 'grant'"
                            >
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium">
                                so'm
                            </span>
                        </div>
                        <p v-if="form.errors.amount" class="err">{{ form.errors.amount }}</p>
                        <p v-if="form.payment_type === 'grant'" class="text-xs text-green-600 mt-1">
                            Grant bo'lganligi uchun summa 0 so'm
                        </p>
                        <p v-else-if="form.amount" class="text-xs text-gray-400 mt-1">
                            {{ formatAmount(form.amount) }}
                        </p>
                    </div>

                </div>

                <!-- Tugmalar -->
                <div class="flex gap-3 mt-6">
                    <Link
                        :href="route('admin.contracts.index')"
                        class="btn-secondary flex-1 flex items-center justify-center gap-2"
                    >
                        <Icon icon="mdi:close" class="w-4 h-4" />
                        Bekor qilish
                    </Link>
                    <button
                        type="button"
                        @click="submit"
                        :disabled="form.processing"
                        class="btn-primary flex-1"
                    >
                        <Icon v-if="form.processing" icon="mdi:loading" class="w-4 h-4 animate-spin" />
                        <Icon v-else icon="mdi:content-save-outline" class="w-4 h-4" />
                        {{ form.processing ? 'Saqlanmoqda...' : 'Yaratish' }}
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
    applicants: { type: Array, default: () => [] },
})

const form = useForm({
    applicant_id: '',
    direction_id: '',
    payment_type: 'contract',
    amount:       0,
})

const selectedApplicant = computed(() =>
    props.applicants.find(a => a.id == form.applicant_id) || null
)

const onApplicantChange = () => {
    if (selectedApplicant.value) {
        form.direction_id = selectedApplicant.value.direction_id
        form.amount       = selectedApplicant.value.amount || 0
    }
}

const paymentTypes = [
    { value: 'contract', label: 'Kontrakt', desc: "Pullik ta'lim", icon: 'mdi:file-sign' },
    { value: 'grant',    label: 'Grant',    desc: "Bepul ta'lim",  icon: 'mdi:medal-outline' },
]

const submit = () => {
    if (form.payment_type === 'grant') form.amount = 0
    form.post(route('admin.contracts.store'))
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
.req { color: #ef4444; }
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
.field-input:focus { border-color: #0f3460; background: white; }
.field-input:disabled { opacity: 0.6; cursor: not-allowed; }
.field-error { border-color: #f87171 !important; background: #fef2f2 !important; }
.err { color: #ef4444; font-size: 0.7rem; margin-top: 0.25rem; display: block; }

.btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.5rem;
    border-radius: 0.75rem;
    background: linear-gradient(135deg, #0f3460, #533483);
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-primary:hover { box-shadow: 0 6px 20px rgba(15,52,96,0.3); }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }

.btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border-radius: 0.75rem;
    background: white;
    color: #374151;
    font-size: 0.875rem;
    font-weight: 600;
    border: 1.5px solid #e5e7eb;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
}
.btn-secondary:hover { background: #f9fafb; }
</style>
