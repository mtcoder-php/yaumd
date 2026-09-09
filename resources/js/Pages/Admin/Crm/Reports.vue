<template>
    <AppLayout title="CRM hisobotlari">
        <div class="space-y-6">

            <div>
                <h1 class="text-xl font-bold text-gray-900">CRM hisobotlari</h1>
                <p class="text-sm text-gray-500 mt-0.5">Talabalar, qarzdorlik va muloqot bo'yicha umumiy ko'rinish</p>
            </div>

            <!-- KPI kartalar -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div v-for="card in statCards" :key="card.label"
                     class="bg-white rounded-2xl border border-gray-100 p-5 flex items-start gap-4"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                         :style="{ background: card.bg }">
                        <Icon :icon="card.icon" class="w-5 h-5 text-white" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-400 mb-0.5">{{ card.label }}</p>
                        <p class="text-2xl font-bold text-gray-900 truncate" :title="card.fullValue">{{ card.value }}</p>
                    </div>
                </div>
            </div>

            <!-- Qarzdorlar ulushi (meter) -->
            <div class="bg-white rounded-2xl border border-gray-100 p-5"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-sm font-bold text-gray-700">Qarzdorlar ulushi</h2>
                    <span class="text-sm font-bold" style="color:#0f3460">{{ debtorSharePercent }}%</span>
                </div>
                <div class="h-3 rounded-full overflow-hidden" style="background: rgba(15,52,96,0.10)">
                    <div class="h-full rounded-full transition-all duration-500"
                         :style="{ width: debtorSharePercent + '%', background: 'linear-gradient(90deg,#0f3460,#533483)' }" />
                </div>
                <p class="text-xs text-gray-400 mt-2">
                    {{ kpi.debtors_count }} / {{ contractStudentsTotal }} kontrakt asosidagi talaba qarzdor
                </p>
            </div>

            <!-- Moliyaviy maqsad: hammasi to'lansa qancha, hozircha qancha yig'ilgan -->
            <div class="bg-white rounded-2xl border border-gray-100 p-5"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <h2 class="text-sm font-bold text-gray-700 mb-3">Kontrakt to'lovlari — maqsad va yig'ilgan</h2>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="card in financeCards" :key="card.label">
                        <p class="text-xs text-gray-400 mb-0.5">{{ card.label }}</p>
                        <p class="text-lg font-bold" :style="{ color: card.color }" :title="card.fullValue">{{ card.value }}</p>
                    </div>
                </div>
                <div class="h-3 rounded-full overflow-hidden mt-4" style="background: rgba(15,52,96,0.10)">
                    <div class="h-full rounded-full transition-all duration-500"
                         :style="{ width: collectedSharePercent + '%', background: 'linear-gradient(90deg,#15803d,#22c55e)' }" />
                </div>
                <p class="text-xs text-gray-400 mt-2">
                    Maqsaddan {{ collectedSharePercent }}% yig'ilgan ({{ formatAmount(kpi.collected_total) }} / {{ formatAmount(kpi.contract_target_total) }})
                </p>
            </div>

            <!-- Chegirma ulushi (meter) -->
            <div class="bg-white rounded-2xl border border-gray-100 p-5"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-sm font-bold text-gray-700">Chegirma tufayli "yo'qotilgan" ulush</h2>
                    <span class="text-sm font-bold" style="color:#7c3aed">{{ kpi.discount_percent_of_gross || 0 }}%</span>
                </div>
                <div class="h-3 rounded-full overflow-hidden" style="background: rgba(124,58,237,0.10)">
                    <div class="h-full rounded-full transition-all duration-500"
                         :style="{ width: (kpi.discount_percent_of_gross || 0) + '%', background: 'linear-gradient(90deg,#7c3aed,#c026d3)' }" />
                </div>
                <p class="text-xs text-gray-400 mt-2">
                    Chegirmasiz {{ formatAmount(kpi.gross_potential_total) }} bo'lardi, chegirma bilan {{ formatAmount(kpi.contract_target_total) }}
                    — {{ kpi.students_with_discount || 0 }} talabaga chegirma berilgan
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                <!-- Yo'nalish bo'yicha -->
                <div class="bg-white rounded-2xl border border-gray-100 p-5"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <h2 class="text-sm font-bold text-gray-700 mb-2">Yo'nalish bo'yicha talabalar</h2>
                    <div v-if="!byDirection.length" class="text-sm text-gray-400 text-center py-10">Ma'lumot yo'q</div>
                    <apexchart v-else type="bar" :height="directionChartHeight"
                               :options="horizontalBarOptions(byDirection)" :series="barSeries(byDirection)" />
                </div>

                <!-- O'quv yili bo'yicha -->
                <div class="bg-white rounded-2xl border border-gray-100 p-5"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <h2 class="text-sm font-bold text-gray-700 mb-2">O'quv yili bo'yicha talabalar</h2>
                    <div v-if="!byAcademicYear.length" class="text-sm text-gray-400 text-center py-10">Ma'lumot yo'q</div>
                    <apexchart v-else type="bar" :height="academicYearChartHeight"
                               :options="horizontalBarOptions(byAcademicYear)" :series="barSeries(byAcademicYear)" />
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                <!-- Kurs bo'yicha -->
                <div class="bg-white rounded-2xl border border-gray-100 p-5"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <h2 class="text-sm font-bold text-gray-700 mb-2">Kurs bo'yicha talabalar</h2>
                    <div v-if="!byCourse.length" class="text-sm text-gray-400 text-center py-10">Ma'lumot yo'q</div>
                    <apexchart v-else type="bar" height="240"
                               :options="courseChartOptions" :series="barSeries(byCourse)" />
                </div>

                <!-- Oxirgi muloqotlar -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="p-5 pb-0">
                        <h2 class="text-sm font-bold text-gray-700">Oxirgi muloqotlar</h2>
                    </div>
                    <div v-if="!recentCommunications.length" class="p-10 text-center text-gray-400">
                        <Icon icon="mdi:phone-off-outline" class="w-8 h-8 mx-auto mb-2 opacity-40" />
                        <p class="text-sm">Hozircha muloqot qayd etilmagan</p>
                    </div>
                    <div v-else class="divide-y divide-gray-50 mt-2">
                        <div v-for="log in recentCommunications" :key="log.id" class="flex items-start gap-3 px-5 py-3">
                            <Icon :icon="typeIcon(log.type)" class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-800">
                                    <span class="font-semibold">{{ log.subject_name }}</span>
                                    — {{ log.summary }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">{{ log.creator || '—' }} · {{ formatDateTime(log.occurred_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

                <!-- Chegirma sababi bo'yicha -->
                <div class="bg-white rounded-2xl border border-gray-100 p-5"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <h2 class="text-sm font-bold text-gray-700 mb-2">Chegirma sababi bo'yicha</h2>
                    <div v-if="!discountByReason.length" class="text-sm text-gray-400 text-center py-10">
                        Hozircha chegirma berilgan talaba yo'q
                    </div>
                    <template v-else>
                        <apexchart type="bar" :height="Math.max(180, discountByReason.length * 42)"
                                   :options="horizontalBarOptions(discountByReason)" :series="barSeries(discountByReason, 'Talabalar')" />
                        <div class="divide-y divide-gray-50 mt-1">
                            <div v-for="row in discountByReason" :key="row.label" class="flex items-center justify-between py-1.5 text-xs">
                                <span class="text-gray-500">{{ row.label }}</span>
                                <span class="font-semibold text-gray-700">{{ formatAmount(row.amount) }} chegirma</span>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Chegirma foizi bo'yicha -->
                <div class="bg-white rounded-2xl border border-gray-100 p-5"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <h2 class="text-sm font-bold text-gray-700 mb-2">Chegirma foizi bo'yicha talabalar soni</h2>
                    <div v-if="!discountByPercent.length" class="text-sm text-gray-400 text-center py-10">
                        Hozircha chegirma berilgan talaba yo'q
                    </div>
                    <apexchart v-else type="bar" height="240"
                               :options="columnChartOptions(discountByPercent)" :series="barSeries(discountByPercent, 'Talabalar')" />
                </div>
            </div>

            <!-- Oylik to'lovlar dinamikasi -->
            <div class="bg-white rounded-2xl border border-gray-100 p-5"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="flex items-center justify-between mb-2">
                    <h2 class="text-sm font-bold text-gray-700">Oylik to'lovlar dinamikasi (oxirgi 6 oy)</h2>
                    <span v-if="latestChangePercent !== null"
                          class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold"
                          :class="latestChangePercent >= 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'">
                        <Icon :icon="latestChangePercent >= 0 ? 'mdi:trending-up' : 'mdi:trending-down'" class="w-3.5 h-3.5" />
                        {{ latestChangePercent >= 0 ? '+' : '' }}{{ latestChangePercent }}% o'tgan oyga nisbatan
                    </span>
                </div>
                <div v-if="!monthlyPayments.length" class="text-sm text-gray-400 text-center py-10">Ma'lumot yo'q</div>
                <apexchart v-else type="area" height="260"
                           :options="areaChartOptions(monthlyLabels)" :series="[{ name: `To'langan summa`, data: monthlyAmounts }]" />
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import {
    compactNumber,
    formatAmount,
    barSeries,
    horizontalBarOptions,
    columnChartOptions,
    areaChartOptions,
} from '@/lib/charts'

const props = defineProps({
    kpi:                  { type: Object, default: () => ({}) },
    byDirection:          { type: Array,  default: () => [] },
    byCourse:             { type: Array,  default: () => [] },
    byAcademicYear:       { type: Array,  default: () => [] },
    recentCommunications: { type: Array,  default: () => [] },
    discountByReason:     { type: Array,  default: () => [] },
    discountByPercent:    { type: Array,  default: () => [] },
    monthlyPayments:      { type: Array,  default: () => [] },
})

const statCards = computed(() => [
    {
        label: 'Jami talabalar',
        value: compactNumber(props.kpi.students_total),
        fullValue: props.kpi.students_total,
        icon: 'mdi:account-school-outline',
        bg: 'linear-gradient(135deg, #0f3460, #533483)',
    },
    {
        label: 'Qarzdorlar soni',
        value: compactNumber(props.kpi.debtors_count),
        fullValue: props.kpi.debtors_count,
        icon: 'mdi:account-alert-outline',
        bg: 'linear-gradient(135deg, #d97706, #f59e0b)',
    },
    {
        label: 'Jami qarz summasi',
        value: compactNumber(props.kpi.debtors_amount) + " so'm",
        fullValue: formatAmount(props.kpi.debtors_amount),
        icon: 'mdi:cash-remove',
        bg: 'linear-gradient(135deg, #b91c1c, #ef4444)',
    },
    {
        label: "Shu oy to'langan",
        value: compactNumber(props.kpi.paid_this_month) + " so'm",
        fullValue: formatAmount(props.kpi.paid_this_month),
        icon: 'mdi:cash-check',
        bg: 'linear-gradient(135deg, #15803d, #22c55e)',
    },
])

const contractStudentsTotal = computed(() => props.kpi.contract_students_total || 0)
const debtorSharePercent = computed(() => {
    const total = contractStudentsTotal.value
    return total > 0 ? Math.round((props.kpi.debtors_count || 0) / total * 100) : 0
})

const directionChartHeight = computed(() => Math.max(220, props.byDirection.length * 38))
const academicYearChartHeight = computed(() => Math.max(160, props.byAcademicYear.length * 42))

// Kurs bo'yicha ustun grafik — umumiy modul (@/lib/charts) Dashboard.vue
// bilan bir xil naqshni ta'minlaydi.
const courseChartOptions = computed(() => columnChartOptions(props.byCourse))

// "Hammasi to'lansa qancha bo'ladi" / "hozircha qancha yig'ilgan" — CRM
// so'rovidagi asosiy savol shu 4 kartada aks etadi.
const financeCards = computed(() => [
    {
        label: 'Kontrakt maqsadi (chegirma bilan)',
        value: compactNumber(props.kpi.contract_target_total) + " so'm",
        fullValue: formatAmount(props.kpi.contract_target_total),
        color: '#0f3460',
    },
    {
        label: "Hozircha yig'ilgan",
        value: compactNumber(props.kpi.collected_total) + " so'm",
        fullValue: formatAmount(props.kpi.collected_total),
        color: '#15803d',
    },
    {
        label: 'Chegirmasiz bo\'lardi',
        value: compactNumber(props.kpi.gross_potential_total) + " so'm",
        fullValue: formatAmount(props.kpi.gross_potential_total),
        color: '#6b7280',
    },
    {
        label: 'Chegirma summasi',
        value: compactNumber(props.kpi.discount_amount_total) + " so'm",
        fullValue: formatAmount(props.kpi.discount_amount_total),
        color: '#7c3aed',
    },
])

const collectedSharePercent = computed(() => {
    const target = props.kpi.contract_target_total || 0
    return target > 0 ? Math.round(((props.kpi.collected_total || 0) / target) * 100) : 0
})

// Oylik to'lovlar dinamikasi — backend "YYYY-MM" qaytaradi. Oy nomi
// uchun brauzerning 'uz-UZ' locale'iga ishonilmaydi — ba'zi brauzerlarda
// ICU ma'lumotlari to'liq bo'lmagani uchun "M10" kabi noto'g'ri qisqartma
// chiqarib yuboradi (Applicants/Edit.vue va Admission/Create.vue'dagi kabi
// qo'lda tayyorlangan oy nomlari ishlatiladi).
const MONTHS_SHORT = ['Yan', 'Fev', 'Mar', 'Apr', 'May', 'Iyun', 'Iyul', 'Avg', 'Sen', 'Okt', 'Noy', 'Dek']
const monthlyLabels = computed(() =>
    props.monthlyPayments.map((m) => {
        const [year, month] = m.month.split('-').map(Number)
        return `${MONTHS_SHORT[month - 1]} ${String(year).slice(2)}`
    })
)
const monthlyAmounts = computed(() => props.monthlyPayments.map((m) => m.amount))
const latestChangePercent = computed(() => {
    const last = props.monthlyPayments[props.monthlyPayments.length - 1]
    return last && last.change_percent !== null && last.change_percent !== undefined ? last.change_percent : null
})

const typeIcon = (v) => ({
    call:    'mdi:phone-outline',
    email:   'mdi:email-outline',
    meeting: 'mdi:account-group-outline',
    note:    'mdi:note-text-outline',
}[v] || 'mdi:note-text-outline')

const formatDateTime = (v) => v
    ? new Date(v).toLocaleString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })
    : '—'
</script>
