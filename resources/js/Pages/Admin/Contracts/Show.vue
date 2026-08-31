<template>
    <AppLayout title="Kontrakt">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.contracts.index')"
                    class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition"
                >
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>


                <div>
                    <h1 class="text-xl font-bold text-gray-900">{{ contract.contract_number }}</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Kontrakt tafsiloti</p>
                </div>
                <div class="ml-auto flex items-center gap-3">
                    <span class="inline-flex items-center px-3 py-1.5 rounded-xl text-sm font-semibold"
                          :class="statusBadge(contract.status)">
                        {{ statusLabel(contract.status) }}
                    </span>
                </div>
                <Link
                    :href="route('admin.contracts.edit', contract.id)"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white"
                    style="background: linear-gradient(135deg, #0f3460, #533483)"
                >
                    <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                    Tahrirlash
                </Link>
                <a :href="route('admin.contracts.pdf', contract.id)"
                   target="_blank"
                   class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 hover:bg-gray-50">
                    <Icon icon="mdi:file-pdf-box" class="w-4 h-4 text-red-500" />
                    PDF yuklash
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                <!-- Chap ustun -->
                <div class="lg:col-span-2 space-y-5">

                    <!-- Abituriyent ma'lumotlari -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5"
                         style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                        <h2 class="section-title">
                            <Icon icon="mdi:account-outline" class="w-4 h-4 text-[#0f3460]" />
                            Abituriyent ma'lumotlari
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="info-label">Familiya</p>
                                <p class="info-value">{{ contract.applicant?.first_name }}</p>
                            </div>
                            <div>
                                <p class="info-label">Ism</p>
                                <p class="info-value">{{ contract.applicant?.last_name }}</p>
                            </div>
                            <div>
                                <p class="info-label">Otasining ismi</p>
                                <p class="info-value">{{ contract.applicant?.middle_name }}</p>
                            </div>
                            <div>
                                <p class="info-label">Pasport seriyasi</p>
                                <p class="info-value font-mono font-semibold">{{ contract.applicant?.passport_series }}</p>
                            </div>
                            <div>
                                <p class="info-label">Telefon</p>
                                <a :href="`tel:${contract.applicant?.phone}`"
                                   class="info-value text-[#0f3460] hover:underline">
                                    {{ contract.applicant?.phone }}
                                </a>
                            </div>
                            <div>
                                <p class="info-label">Manzil</p>
                                <p class="info-value">
                                    {{ contract.applicant?.region?.name_uz }}
                                    {{ contract.applicant?.district?.name_uz ? ', ' + contract.applicant.district.name_uz : '' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Ta'lim ma'lumotlari -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5"
                         style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                        <h2 class="section-title">
                            <Icon icon="mdi:school-outline" class="w-4 h-4 text-[#0f3460]" />
                            Ta'lim ma'lumotlari
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="col-span-2">
                                <p class="info-label">Yo'nalish</p>
                                <p class="info-value font-semibold">{{ contract.direction?.name_uz || '—' }}</p>
                                <p class="text-xs text-gray-400">{{ contract.direction?.faculty?.name_uz || '' }}</p>
                            </div>
                            <div>
                                <p class="info-label">To'lov turi</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                      :class="contract.payment_type === 'grant'
                                        ? 'bg-green-50 text-green-700'
                                        : 'bg-blue-50 text-blue-700'">
                                    {{ contract.payment_type === 'grant' ? 'Grant' : 'Kontrakt' }}
                                </span>
                            </div>
                            <div>
                                <p class="info-label">Kontrakt summasi</p>
                                <p class="text-lg font-bold text-[#0f3460]">{{ formatAmount(contract.amount) }}</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- O'ng ustun -->
                <div class="space-y-5">

                    <!-- Status o'zgartirish -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5"
                         style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                        <h2 class="section-title">Status</h2>
                        <div class="flex flex-col gap-2">
                            <button
                                v-for="s in statuses"
                                :key="s.value"
                                @click="updateStatus(s.value)"
                                class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium transition-all text-left"
                                :class="contract.status === s.value
                                    ? s.class + ' ring-2 ring-offset-1 ring-[#0f3460]'
                                    : s.class + ' opacity-60 hover:opacity-100'"
                            >
                                <Icon :icon="s.icon" class="w-4 h-4 flex-shrink-0" />
                                {{ s.label }}
                                <Icon v-if="contract.status === s.value" icon="mdi:check" class="w-4 h-4 ml-auto" />
                            </button>
                        </div>
                    </div>

                    <!-- Kontrakt ma'lumotlari -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5"
                         style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                        <h2 class="section-title">Kontrakt</h2>
                        <div class="space-y-3">
                            <div>
                                <p class="info-label">Kontrakt raqami</p>
                                <p class="text-sm font-mono font-bold text-[#0f3460]">{{ contract.contract_number }}</p>
                            </div>
                            <div>
                                <p class="info-label">Yaratilgan sana</p>
                                <p class="info-value">{{ formatDate(contract.created_at) }}</p>
                            </div>
                            <div v-if="contract.signed_at">
                                <p class="info-label">Imzolangan sana</p>
                                <p class="info-value">{{ formatDate(contract.signed_at) }}</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    contract: { type: Object, required: true },
})

const statuses = [
    { value: 'draft',     label: 'Qoralama',  icon: 'mdi:file-outline',         class: 'bg-yellow-50 text-yellow-700' },
    { value: 'signed',    label: 'Imzolandi', icon: 'mdi:file-sign',             class: 'bg-blue-50 text-blue-700' },
    { value: 'paid',      label: "To'landi",  icon: 'mdi:check-circle-outline',  class: 'bg-green-50 text-green-700' },
    { value: 'cancelled', label: 'Bekor',     icon: 'mdi:close-circle-outline',  class: 'bg-red-50 text-red-700' },
]

const statusLabel = (s) => statuses.find(x => x.value === s)?.label || s
const statusBadge = (s) => statuses.find(x => x.value === s)?.class || 'bg-gray-100 text-gray-600'

const updateStatus = (status) => {
    router.put(route('admin.contracts.update', props.contract.id), {
        amount:       props.contract.amount,
        payment_type: props.contract.payment_type,
        status,
    }, {
        preserveState: true,
        preserveScroll: true,
    })
}

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
.section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.75rem;
    font-weight: 700;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 1rem;
}
.info-label {
    font-size: 0.7rem;
    font-weight: 600;
    color: #9ca3af;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-bottom: 0.25rem;
}
.info-value {
    font-size: 0.875rem;
    color: #111827;
}
</style>
