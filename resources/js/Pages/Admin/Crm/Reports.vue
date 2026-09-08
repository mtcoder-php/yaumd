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
                               :options="columnChartOptions" :series="barSeries(byCourse)" />
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
    columnChartOptions as buildColumnChartOptions,
} from '@/lib/charts'

const props = defineProps({
    kpi:                  { type: Object, default: () => ({}) },
    byDirection:          { type: Array,  default: () => [] },
    byCourse:             { type: Array,  default: () => [] },
    byAcademicYear:       { type: Array,  default: () => [] },
    recentCommunications: { type: Array,  default: () => [] },
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
const columnChartOptions = computed(() => buildColumnChartOptions(props.byCourse))

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
