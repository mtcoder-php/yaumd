<template>
    <AppLayout :title="`${applicant.last_name} ${applicant.first_name}`">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center gap-4">
                <Link :href="route('admin.applicants.index')"
                      class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition">
                    <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">
                        {{ applicant.last_name }} {{ applicant.first_name }} {{ applicant.middle_name }}
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">{{ applicant.application_number }}</p>
                </div>
                <div class="ml-auto flex items-center gap-3">
                    <Link :href="route('admin.applicants.edit', applicant.id)"
                          class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition"
                          style="background: linear-gradient(135deg, #0f3460, #533483)">
                        <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                        Tahrirlash
                    </Link>

                    <!-- Status dropdown -->
                    <select
                        :value="applicant.status"
                        class="px-3 py-2 text-sm font-semibold rounded-xl border-2 cursor-pointer outline-none"
                        :class="statusBadge(applicant.status)"
                        @change="updateStatus($event.target.value)"
                    >
                        <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                <!-- Chap ustun -->
                <div class="lg:col-span-2 space-y-5">

                    <!-- Ta'lim ma'lumotlari -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5"
                         style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                        <h2 class="section-title">
                            <Icon icon="mdi:school-outline" class="w-4 h-4 text-[#0f3460]" />
                            Ta'lim ma'lumotlari
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="info-label">Ta'lim turi</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                                    {{ educationTypeLabel(applicant.education_type) }}
                                </span>
                            </div>
                            <div>
                                <p class="info-label">Ta'lim shakli</p>
                                <p class="info-value">{{ studyFormLabel(applicant.study_form) }}</p>
                            </div>
                            <div class="col-span-2">
                                <p class="info-label">Yo'nalish</p>
                                <p class="info-value font-semibold">{{ applicant.direction?.name_uz || '—' }}</p>
                                <p class="text-xs text-gray-400">{{ applicant.direction?.faculty?.name_uz || '' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Shaxsiy ma'lumotlar -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5"
                         style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                        <h2 class="section-title">
                            <Icon icon="mdi:account-outline" class="w-4 h-4 text-[#0f3460]" />
                            Shaxsiy ma'lumotlar
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="info-label">Familiya</p>
                                <p class="info-value">{{ applicant.last_name }}</p>
                            </div>
                            <div>
                                <p class="info-label">Ism</p>
                                <p class="info-value">{{ applicant.first_name }}</p>
                            </div>
                            <div>
                                <p class="info-label">Otasining ismi</p>
                                <p class="info-value">{{ applicant.middle_name }}</p>
                            </div>
                            <div>
                                <p class="info-label">Jins</p>
                                <p class="info-value">{{ applicant.gender === 'male' ? 'Erkak' : 'Ayol' }}</p>
                            </div>
                            <div>
                                <p class="info-label">Tug'ilgan sana</p>
                                <p class="info-value">{{ applicant.birth_day }}/{{ applicant.birth_month }}/{{ applicant.birth_year }}</p>
                            </div>
                            <div>
                                <p class="info-label">Millati</p>
                                <p class="info-value">{{ applicant.nationality || '—' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Hujjat va aloqa -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5"
                         style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                        <h2 class="section-title">
                            <Icon icon="mdi:card-account-details-outline" class="w-4 h-4 text-[#0f3460]" />
                            Hujjat va aloqa
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="info-label">Pasport seriyasi</p>
                                <p class="info-value font-mono font-bold">{{ applicant.passport_series }}</p>
                            </div>
                            <div>
                                <p class="info-label">JSHSHIR</p>
                                <p class="info-value font-mono">{{ applicant.jshshir || '—' }}</p>
                            </div>
                            <div>
                                <p class="info-label">Telefon</p>
                                <a :href="`tel:${applicant.phone}`" class="info-value text-[#0f3460] hover:underline">
                                    {{ applicant.phone }}
                                </a>
                            </div>
                            <div>
                                <p class="info-label">Qo'shimcha raqam</p>
                                <a v-if="applicant.extra_phone" :href="`tel:${applicant.extra_phone}`"
                                   class="info-value text-[#0f3460] hover:underline">
                                    {{ applicant.extra_phone }}
                                </a>
                                <p v-else class="info-value">—</p>
                            </div>
                            <div class="col-span-2">
                                <p class="info-label">Manzil</p>
                                <p class="info-value">
                                    {{ applicant.region?.name_uz }}{{ applicant.district ? ' , ' + applicant.district.name_uz : '' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Suhbat -->
                    <div v-if="applicant.interview" class="bg-white rounded-2xl border border-gray-100 p-5"
                         style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                        <h2 class="section-title">
                            <Icon icon="mdi:account-check-outline" class="w-4 h-4 text-[#0f3460]" />
                            Suhbat natijasi
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="info-label">Natija</p>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                      :class="applicant.interview.result === 'passed'
                                        ? 'bg-green-50 text-green-700'
                                        : 'bg-red-50 text-red-700'">
                                    <Icon :icon="applicant.interview.result === 'passed'
                                        ? 'mdi:check-circle' : 'mdi:close-circle'" class="w-3.5 h-3.5" />
                                    {{ applicant.interview.result === 'passed' ? "O'tdi" : "O'tmadi" }}
                                </span>
                            </div>
                            <div>
                                <p class="info-label">Kim o'tkazdi</p>
                                <p class="info-value">{{ applicant.interview.interviewer?.full_name || '—' }}</p>
                            </div>
                            <div v-if="applicant.interview.notes" class="col-span-2">
                                <p class="info-label">Izoh</p>
                                <p class="info-value">{{ applicant.interview.notes }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Test sessiyasi -->
                    <div v-if="applicant.test_session" class="bg-white rounded-2xl border border-gray-100 p-5"
                         style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                        <h2 class="section-title">
                            <Icon icon="mdi:clipboard-text-outline" class="w-4 h-4 text-[#0f3460]" />
                            Test ma'lumotlari
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="info-label">Login</p>
                                <p class="info-value font-mono font-bold text-[#0f3460]">{{ applicant.test_session.login }}</p>
                            </div>
                            <div>
                                <p class="info-label">Parol</p>
                                <p class="info-value font-mono">{{ applicant.test_session.password_plain }}</p>
                            </div>
                            <div>
                                <p class="info-label">Status</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                      :class="{
                                        'bg-yellow-50 text-yellow-700': applicant.test_session.status === 'pending',
                                        'bg-green-50 text-green-700':  applicant.test_session.status === 'active',
                                        'bg-blue-50 text-blue-700':    applicant.test_session.status === 'completed',
                                        'bg-red-50 text-red-700':      applicant.test_session.status === 'expired',
                                    }">
                                    {{ {pending:'Kutilmoqda', active:'Faol', completed:'Yakunlangan', expired:"Muddati o'tgan"}[applicant.test_session.status] }}
                                </span>
                            </div>
                            <div v-if="applicant.test_session.score !== null">
                                <p class="info-label">Ball</p>
                                <p class="text-lg font-bold text-green-600">{{ applicant.test_session.score }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Kontrakt -->
                    <div v-if="applicant.contract" class="bg-white rounded-2xl border border-gray-100 p-5"
                         style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                        <h2 class="section-title">
                            <Icon icon="mdi:file-document-outline" class="w-4 h-4 text-[#0f3460]" />
                            Kontrakt
                        </h2>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="info-label">Kontrakt raqami</p>
                                <p class="text-sm font-mono font-bold text-[#0f3460]">{{ applicant.contract.contract_number }}</p>
                            </div>
                            <div>
                                <p class="info-label">Kontrakt summasi</p>
                                <p class="text-sm font-bold text-gray-800">{{ formatAmount(applicant.contract.amount) }}</p>
                            </div>
                            <div>
                                <p class="info-label">Status</p>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                      :class="{
                                        'bg-yellow-50 text-yellow-700': applicant.contract.status === 'draft',
                                        'bg-blue-50 text-blue-700':    applicant.contract.status === 'signed',
                                        'bg-green-50 text-green-700':  applicant.contract.status === 'paid',
                                        'bg-red-50 text-red-700':      applicant.contract.status === 'cancelled',
                                    }">
                                    {{ {draft:'Qoralama', signed:'Imzolandi', paid:"To'landi", cancelled:'Bekor'}[applicant.contract.status] }}
                                </span>
                            </div>
                            <div>
                                <p class="info-label">To'langan summa</p>
                                <p class="text-sm font-bold text-green-600">
                                    {{ formatAmount(paidAmount) }}
                                    <span class="text-xs text-gray-400 font-normal">/ {{ formatAmount(applicant.contract.amount) }}</span>
                                </p>
                            </div>
                        </div>

                        <!-- To'lovlar tarixi -->
                        <div v-if="applicant.contract.payments?.length" class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">To'lovlar tarixi</p>
                            <div class="space-y-2">
                                <div v-for="pay in applicant.contract.payments" :key="pay.id"
                                     class="flex items-center justify-between py-2 border-b border-gray-50">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs text-gray-400">{{ formatDate(pay.paid_at) }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700">
                                            {{ {cash:'Naqd', click:'Click', payme:'Payme'}[pay.provider] }}
                                        </span>
                                    </div>
                                    <span class="text-sm font-bold text-gray-800">{{ formatAmount(pay.amount) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- PDF -->
                        <div class="mt-4">
                            <a :href="route('admin.contracts.pdf', applicant.contract.id)"
                               target="_blank"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold border border-gray-200 hover:bg-gray-50 transition">
                                <Icon icon="mdi:file-pdf-box" class="w-4 h-4 text-red-500" />
                                PDF yuklash
                            </a>
                        </div>
                    </div>

                </div>

                <!-- O'ng ustun -->
                <div class="space-y-5">

                    <!-- Status -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5"
                         style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                        <h2 class="section-title">Status</h2>
                        <div class="flex flex-col gap-2">
                            <button v-for="s in statuses" :key="s.value"
                                    @click="updateStatus(s.value)"
                                    class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium transition-all text-left"
                                    :class="applicant.status === s.value
                                    ? s.activeClass + ' ring-2 ring-offset-1 ring-[#0f3460]'
                                    : s.activeClass + ' opacity-50 hover:opacity-100'">
                                <Icon :icon="s.icon" class="w-4 h-4 flex-shrink-0" />
                                {{ s.label }}
                                <Icon v-if="applicant.status === s.value" icon="mdi:check" class="w-4 h-4 ml-auto" />
                            </button>
                        </div>
                    </div>

                    <!-- Ariza -->
                    <div class="bg-white rounded-2xl border border-gray-100 p-5"
                         style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                        <h2 class="section-title">Ariza</h2>
                        <div class="space-y-3">
                            <div>
                                <p class="info-label">Ariza raqami</p>
                                <p class="text-sm font-mono font-bold text-[#0f3460]">{{ applicant.application_number }}</p>
                            </div>
                            <div>
                                <p class="info-label">Topshirilgan sana</p>
                                <p class="info-value">{{ formatDate(applicant.created_at) }}</p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    applicant: { type: Object, required: true },
})

const paidAmount = computed(() =>
    props.applicant.contract?.payments?.reduce((sum, p) => sum + Number(p.amount), 0) || 0
)

const statuses = [
    { value: 'new',        label: 'Yangi',            icon: 'mdi:file-outline',          activeClass: 'bg-blue-50 text-blue-700' },
    { value: 'accepted',   label: 'Qabul qilindi',    icon: 'mdi:check-circle-outline',  activeClass: 'bg-green-50 text-green-700' },
    { value: 'interview',  label: 'Suhbat',           icon: 'mdi:account-voice',         activeClass: 'bg-yellow-50 text-yellow-700' },
    { value: 'tested',     label: 'Test',             icon: 'mdi:clipboard-text-outline', activeClass: 'bg-purple-50 text-purple-700' },
    { value: 'contracted', label: 'Kontrakt',         icon: 'mdi:file-sign',             activeClass: 'bg-indigo-50 text-indigo-700' },
    { value: 'enrolled',   label: "Ro'yxatga olindi", icon: 'mdi:school-outline',        activeClass: 'bg-teal-50 text-teal-700' },
    { value: 'rejected',   label: 'Rad etildi',       icon: 'mdi:close-circle-outline',  activeClass: 'bg-red-50 text-red-700' },
]

const statusBadge = (status) => {
    const badges = {
        new:        'bg-blue-50 text-blue-700 border-blue-200',
        accepted:   'bg-green-50 text-green-700 border-green-200',
        interview:  'bg-yellow-50 text-yellow-700 border-yellow-200',
        tested:     'bg-purple-50 text-purple-700 border-purple-200',
        contracted: 'bg-indigo-50 text-indigo-700 border-indigo-200',
        enrolled:   'bg-teal-50 text-teal-700 border-teal-200',
        rejected:   'bg-red-50 text-red-700 border-red-200',
    }
    return badges[status] || 'bg-gray-50 text-gray-600 border-gray-200'
}

const updateStatus = (status) => {
    if (status === props.applicant.status) return
    router.patch(route('admin.applicants.status', props.applicant.id), { status }, {
        preserveScroll: true,
    })
}

const educationTypeLabel = (type) => ({
    bachelor: 'Bakalavr', master: 'Magistr',
    transfer: 'Transfer', second: '2-mutaxassislik',
}[type] || type)

const studyFormLabel = (form) => ({
    full_time: 'Kunduzgi', evening: 'Kechki', distance: 'Sirtqi',
}[form] || form)

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
