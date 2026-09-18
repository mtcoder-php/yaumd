<template>
    <AppLayout title="Dashboard">
        <div class="space-y-5">

            <!-- Sarlavha — referensdagi (billing.e-edu.uz) "OTM Admin
                 Dashboard" qismiga mos: chap tomonda sarlavha+tashkilot nomi,
                 o'ng tomonda joriy o'quv yili belgisi (agar faol o'quv yili
                 belgilangan bo'lsa). -->
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">OTM Admin Dashboard</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Yangi Asr Universiteti</p>
                </div>
                <span v-if="academicYear" class="btn-brand" style="cursor: default">
                    <Icon icon="mdi:calendar-blank-outline" class="w-4 h-4" />
                    {{ academicYear }} o'quv yili
                </span>
            </div>

            <!-- Stat kartalar — yuqori chegarasi rangli, oq fon, katta qalin
                 son + kichik izoh (referensdagi 7 ta ko'rsatkichga mos). -->
            <div>
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Asosiy ko'rsatkichlar</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div
                        v-for="card in statCards"
                        :key="card.label"
                        class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                        style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)"
                    >
                        <div class="h-1" :style="{ background: card.color }" />
                        <div class="p-4">
                            <p class="text-xs font-medium text-gray-500 mb-1.5">{{ card.label }}</p>
                            <p class="text-2xl font-bold text-gray-900" :title="card.title">{{ card.value }}</p>
                            <p v-if="card.sub" class="text-xs text-gray-400 mt-1">{{ card.sub }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kunlik shartnomalar — davr almashtirgichi (Kun/Oy/Yil) + ikki
                 seriyali (tasdiqlangan/bekor qilingan) maydon-chiziq grafik. -->
            <div class="bg-white rounded-2xl border border-gray-100 p-5"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="flex items-start justify-between flex-wrap gap-3 mb-4">
                    <div>
                        <h2 class="text-sm font-bold text-gray-800">Kunlik shartnomalar</h2>
                        <p class="text-xs text-gray-400 mt-0.5">{{ periodSubtitle }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-4 text-xs">
                            <span class="flex items-center gap-1.5 text-gray-500">
                                <span class="w-2.5 h-2.5 rounded-sm bg-brand-600"></span>
                                Tasdiqlangan
                            </span>
                            <span class="flex items-center gap-1.5 text-gray-500">
                                <span class="w-2.5 h-2.5 rounded-sm bg-red-500"></span>
                                Bekor qilingan
                            </span>
                        </div>
                        <div class="inline-flex items-center gap-1 p-1 bg-gray-100 rounded-lg">
                            <button
                                v-for="opt in periodOptions"
                                :key="opt.value"
                                type="button"
                                @click="period = opt.value"
                                class="px-3 py-1 text-xs font-semibold rounded-md transition"
                                :class="period === opt.value ? 'bg-brand-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                            >
                                {{ opt.label }}
                            </button>
                        </div>
                    </div>
                </div>
                <apexchart type="area" height="260" :options="trendChartOptions" :series="trendSeries" />
            </div>

            <!-- Talabalar → HEMIS → shartnoma konversiya zanjiri -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div class="bg-white rounded-2xl border border-gray-100 p-5"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <h2 class="text-sm font-bold text-gray-800">Talabalar va HEMIS</h2>
                    <p class="text-xs text-gray-400 mt-0.5 mb-3">Konversiya: talaba → HEMIS</p>
                    <div class="flex items-end justify-between mb-2">
                        <span class="text-xs text-gray-500">Jami talaba</span>
                        <span class="text-lg font-bold text-gray-900">{{ compactNumber(hemis.students_total) }}</span>
                    </div>
                    <div class="h-2.5 rounded-full overflow-hidden flex bg-gray-100">
                        <div class="h-full bg-brand-600" :style="{ width: hemisWithPct + '%' }"></div>
                        <div class="h-full bg-orange-400" :style="{ width: (100 - hemisWithPct) + '%' }"></div>
                    </div>
                    <div class="flex items-center justify-between mt-3 text-xs flex-wrap gap-2">
                        <span class="flex items-center gap-1.5 text-gray-500">
                            <span class="w-2 h-2 rounded-full bg-brand-600"></span>
                            HEMIS'da bor: {{ hemis.students_with_hemis }} ({{ hemisWithPct }}%)
                        </span>
                        <span class="flex items-center gap-1.5 text-gray-500">
                            <span class="w-2 h-2 rounded-full bg-orange-400"></span>
                            HEMIS'da yo'q: {{ hemis.students_without_hemis }} ({{ 100 - hemisWithPct }}%)
                        </span>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 p-5"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <h2 class="text-sm font-bold text-gray-800">Talabalar va shartnoma</h2>
                    <p class="text-xs text-gray-400 mt-0.5 mb-3">Konversiya: talaba → shartnoma</p>
                    <div class="flex items-end justify-between mb-2">
                        <span class="text-xs text-gray-500">Jami talaba</span>
                        <span class="text-lg font-bold text-gray-900">{{ compactNumber(hemis.students_total) }}</span>
                    </div>
                    <div class="h-2.5 rounded-full overflow-hidden flex bg-gray-100">
                        <div class="h-full bg-brand-600" :style="{ width: contractWithPct + '%' }"></div>
                        <div class="h-full bg-orange-400" :style="{ width: (100 - contractWithPct) + '%' }"></div>
                    </div>
                    <div class="flex items-center justify-between mt-3 text-xs flex-wrap gap-2">
                        <span class="flex items-center gap-1.5 text-gray-500">
                            <span class="w-2 h-2 rounded-full bg-brand-600"></span>
                            Shartnoma bor: {{ hemis.students_with_contract }} ({{ contractWithPct }}%)
                        </span>
                        <span class="flex items-center gap-1.5 text-gray-500">
                            <span class="w-2 h-2 rounded-full bg-orange-400"></span>
                            Shartnoma yo'q: {{ hemis.students_without_contract }} ({{ 100 - contractWithPct }}%)
                        </span>
                    </div>
                </div>
            </div>

            <!-- Ta'lim shakli / Kurs bo'yicha / Bakalavr-Magistr -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <div class="bg-white rounded-2xl border border-gray-100 p-5"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <h2 class="text-sm font-bold text-gray-800 mb-2">Ta'lim shakli</h2>
                    <p class="text-xs text-gray-400 mb-2">Joriy yil</p>
                    <apexchart type="donut" height="230" :options="studyFormChartOptions" :series="studyFormSeries" />
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 p-5"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <h2 class="text-sm font-bold text-gray-800 mb-2">Kurs bo'yicha</h2>
                    <p class="text-xs text-gray-400 mb-2">Faol talabalar</p>
                    <apexchart type="bar" height="230" :options="courseYearChartOptions" :series="courseYearSeries" />
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 p-5"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <h2 class="text-sm font-bold text-gray-800 mb-2">Bakalavr / Magistr</h2>
                    <p class="text-xs text-gray-400 mb-2">Ta'lim turi</p>
                    <apexchart type="donut" height="230" :options="degreeChartOptions" :series="degreeSeries" />
                </div>
            </div>

            <!-- TOP fakultetlar / TOP yo'nalishlar -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-5"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <h2 class="text-sm font-bold text-gray-800 mb-2">TOP fakultetlar</h2>
                    <p class="text-xs text-gray-400 mb-2">Shartnomalar soni</p>
                    <apexchart type="bar" :height="Math.max(220, topFaculties.length * 40)" :options="facultyChartOptions" :series="facultySeries" />
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 p-5"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <h2 class="text-sm font-bold text-gray-800">TOP yo'nalishlar</h2>
                    <p class="text-xs text-gray-400 mb-3">Eng ko'p tanlangan</p>
                    <div class="flex items-center justify-between text-xs font-semibold text-gray-400 uppercase tracking-wider pb-2 border-b border-gray-100">
                        <span>Yo'nalish</span>
                        <span>Ariza</span>
                    </div>
                    <div class="divide-y divide-gray-50">
                        <div v-if="!topDirections.length" class="py-6 text-center text-xs text-gray-400">Ma'lumot yo'q</div>
                        <div v-for="d in topDirections" :key="d.label" class="flex items-center justify-between py-2.5 gap-3">
                            <span class="text-sm text-gray-700">{{ d.label }}</span>
                            <span class="text-sm font-semibold text-gray-900 flex-shrink-0">{{ d.count }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hududiy demografiya / Moliyaviy quvur -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-5"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <h2 class="text-sm font-bold text-gray-800 mb-2">Hududiy demografiya</h2>
                    <p class="text-xs text-gray-400 mb-2">Abituriyentlar qaysi viloyatdan</p>
                    <apexchart type="bar" height="300" :options="regionalChartOptions" :series="regionalSeries" />
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 p-5"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <h2 class="text-sm font-bold text-gray-800 mb-1">Moliyaviy quvur</h2>
                    <p class="text-xs text-gray-400 mb-3">Shartnomadan to'lovgacha</p>

                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-gray-400 mb-1">Shartnoma summasi</p>
                            <p class="text-sm font-bold text-gray-900">{{ compactAmount(financial.contract_amount) }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3">
                            <p class="text-xs text-gray-400 mb-1">To'langan</p>
                            <p class="text-sm font-bold text-green-600">{{ compactAmount(financial.paid_amount) }}</p>
                        </div>
                        <div class="rounded-xl bg-gray-50 p-3 col-span-2">
                            <p class="text-xs text-gray-400 mb-1">Qoldiq</p>
                            <p class="text-sm font-bold text-orange-500">{{ compactAmount(financial.remaining_amount) }}</p>
                        </div>
                    </div>

                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">To'lov kanali</p>
                    <div class="space-y-2.5">
                        <div v-if="!paymentChannels.length" class="text-center text-xs text-gray-400 py-3">Ma'lumot yo'q</div>
                        <div v-for="ch in paymentChannels" :key="ch.provider">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-medium text-gray-700">{{ providerLabel(ch.provider) }}</span>
                                <span class="text-gray-400">{{ ch.tx_count }} ta · {{ compactAmount(ch.total) }}</span>
                            </div>
                            <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden">
                                <div class="h-full bg-brand-600" :style="{ width: channelPct(ch) + '%' }"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Yangi arizalar (FIFO) — navbat tartibida (eng eski kutayotgan
                 ariza birinchi), referensdagi jadval bilan bir xil to'liq
                 to'r (grid) dizaynda. -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <h2 class="text-sm font-bold text-gray-800">Yangi arizalar (FIFO)</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Tasdiqlanishi kerak</p>
                    </div>
                    <Link
                        :href="route('admin.applicants.index')"
                        class="text-xs font-semibold flex items-center gap-1 text-brand-600 hover:text-brand-700 transition"
                    >
                        Barchasi
                        <Icon icon="mdi:arrow-right" class="w-3.5 h-3.5" />
                    </Link>
                </div>

                <div class="table-grid-wrap">
                    <table class="table-grid">
                        <thead>
                        <tr>
                            <th>Vaqt</th>
                            <th>F.I.Sh</th>
                            <th>Yo'nalish</th>
                            <th>Shakl</th>
                            <th>Holat</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-if="!fifoApplicants.length">
                            <td colspan="5" class="text-center py-10 text-gray-400">Kutayotgan ariza yo'q</td>
                        </tr>
                        <tr v-for="a in fifoApplicants" :key="a.id">
                            <td class="text-xs text-gray-400 whitespace-nowrap">{{ timeAgo(a.created_at) }}</td>
                            <td>
                                <Link
                                    :href="route('admin.applicants.show', a.id)"
                                    class="text-sm font-semibold text-gray-800 hover:text-brand-600 transition"
                                >
                                    {{ a.last_name }} {{ a.first_name }}
                                </Link>
                            </td>
                            <td class="text-sm text-gray-600">{{ a.direction?.name_uz || '—' }}</td>
                            <td class="text-sm text-gray-600">{{ studyFormLabel(a.study_form) }}</td>
                            <td><span class="badge-pill badge-brand">Yangi</span></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import {
    barSeries, horizontalBarOptions, columnChartOptions, donutChartOptions, dualAreaChartOptions,
    compactNumber, compactAmount,
} from '@/lib/charts'

const props = defineProps({
    stats:                  { type: Object, default: () => ({}) },
    academic_year:          { type: String, default: null },
    contracts_trend:        { type: Object, default: () => ({ daily: [], monthly: [], yearly: [] }) },
    hemis_pipeline:         { type: Object, default: () => ({}) },
    student_breakdown:      { type: Object, default: () => ({ study_form: {}, course_year: {}, degree: {} }) },
    top_faculties:          { type: Array,  default: () => [] },
    top_directions:         { type: Array,  default: () => [] },
    regional_demographics:  { type: Array,  default: () => [] },
    financial_pipeline:     { type: Object, default: () => ({ contract_amount: 0, paid_amount: 0, remaining_amount: 0 }) },
    payment_channels:       { type: Array,  default: () => [] },
    fifo_applicants:        { type: Array,  default: () => [] },
})

const academicYear    = computed(() => props.academic_year)
const hemis           = computed(() => props.hemis_pipeline || {})
const financial       = computed(() => props.financial_pipeline || {})
const topFaculties    = computed(() => props.top_faculties || [])
const topDirections   = computed(() => props.top_directions || [])
const paymentChannels = computed(() => props.payment_channels || [])
const fifoApplicants  = computed(() => props.fifo_applicants || [])

// Foizni xavfsiz hisoblash (bo'luvchi 0 bo'lsa — 0%, NaN emas).
const pct = (part, total) => (total > 0 ? Math.round((part / total) * 1000) / 10 : 0)

// --- Stat kartalar ---
const statCards = computed(() => {
    const s = props.stats
    return [
        {
            label: 'Jami arizalar', value: compactNumber(s.applicants_total), title: s.applicants_total,
            color: '#3b82f6', sub: `Bugun: +${s.applicants_today ?? 0}`,
        },
        {
            label: 'Tasdiqlangan shartnomalar', value: compactNumber(s.contracts_confirmed), title: s.contracts_confirmed,
            color: '#22c55e', sub: `Jami shartnomadan ${pct(s.contracts_confirmed, s.contracts)}%`,
        },
        {
            label: 'Shartnoma summasi', value: compactAmount(s.contracts_amount) + " so'm",
            color: '#a855f7', sub: `${compactNumber(s.contracts_confirmed)} ta shartnoma bo'yicha`,
        },
        {
            label: "Tushgan to'lovlar", value: compactAmount(s.payments_total) + " so'm",
            color: '#ec4899', sub: `Shartnoma summasidan ${pct(s.payments_total, s.contracts_amount)}%`,
        },
        {
            label: 'Yangi (kutilmoqda)', value: compactNumber(s.applicants_new), title: s.applicants_new,
            color: '#f59e0b', sub: `Jami arizadan ${pct(s.applicants_new, s.applicants_total)}%`,
        },
        {
            label: 'Bekor qilingan', value: compactNumber(s.contracts_cancelled), title: s.contracts_cancelled,
            color: '#ef4444', sub: `Jami shartnomadan ${pct(s.contracts_cancelled, s.contracts)}%`,
        },
        {
            label: 'Talabalar', value: compactNumber(s.students), title: s.students,
            color: '#6366f1', sub: "Ro'yxatdan o'tgan",
        },
    ]
})

// --- Kunlik shartnomalar (davr almashtirgichli) grafik ---
const periodOptions = [
    { value: 'daily',   label: 'Kun' },
    { value: 'monthly', label: 'Oy' },
    { value: 'yearly',  label: 'Yil' },
]
const period = ref('daily')

const periodSubtitle = computed(() => ({
    daily: "So'nggi 14 kun", monthly: "So'nggi 12 oy", yearly: "So'nggi 5 yil",
}[period.value]))

const trendRows = computed(() => props.contracts_trend?.[period.value] || [])

const MONTH_SHORT = ['Yan', 'Fev', 'Mar', 'Apr', 'May', 'Iyun', 'Iyul', 'Avg', 'Sen', 'Okt', 'Noy', 'Dek']

const formatPeriodLabel = (label) => {
    if (period.value === 'daily') {
        return new Date(label).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit' })
    }
    if (period.value === 'monthly') {
        const [y, m] = label.split('-')
        return `${MONTH_SHORT[parseInt(m, 10) - 1]} ${y.slice(2)}`
    }
    return label
}

const trendSeries = computed(() => [
    { name: 'Tasdiqlangan',   data: trendRows.value.map((r) => r.confirmed) },
    { name: 'Bekor qilingan', data: trendRows.value.map((r) => r.cancelled) },
])
const trendChartOptions = computed(() => dualAreaChartOptions(trendRows.value.map((r) => formatPeriodLabel(r.label))))

// --- HEMIS / shartnoma konversiya ---
const hemisWithPct    = computed(() => pct(hemis.value.students_with_hemis, hemis.value.students_total))
const contractWithPct = computed(() => pct(hemis.value.students_with_contract, hemis.value.students_total))

// --- Ta'lim shakli / Kurs bo'yicha / Bakalavr-Magistr ---
const STUDY_FORM_LABELS = { full_time: 'Kunduzgi', evening: 'Kechki', distance: 'Sirtqi' }
const studyFormLabel = (v) => STUDY_FORM_LABELS[v] || '—'

const studyFormEntries = computed(() => Object.entries(props.student_breakdown?.study_form || {}))
const studyFormSeries = computed(() => studyFormEntries.value.map(([, count]) => count))
const studyFormChartOptions = computed(() => donutChartOptions(
    studyFormEntries.value.map(([key]) => studyFormLabel(key)),
    ['#6366f1', '#14b8a6', '#f59e0b'],
))

const courseYearEntries = computed(() => Object.entries(props.student_breakdown?.course_year || {}).sort((a, b) => Number(a[0]) - Number(b[0])))
const courseYearRows = computed(() => courseYearEntries.value.map(([year, count]) => ({ label: `${year}-kurs`, count })))
const courseYearSeries = computed(() => barSeries(courseYearRows.value, 'Talabalar'))
const courseYearChartOptions = computed(() => columnChartOptions(courseYearRows.value))

const DEGREE_LABELS = { bachelor: 'Bakalavr', master: 'Magistr' }
const degreeEntries = computed(() => Object.entries(props.student_breakdown?.degree || {}))
const degreeSeries = computed(() => degreeEntries.value.map(([, count]) => count))
const degreeChartOptions = computed(() => donutChartOptions(
    degreeEntries.value.map(([key]) => DEGREE_LABELS[key] || key),
    ['#6366f1', '#f472b6'],
))

// --- TOP fakultetlar / hududiy demografiya ---
const facultyRows = computed(() => topFaculties.value.map((f) => ({ label: f.label, count: f.count })))
const facultySeries = computed(() => barSeries(facultyRows.value, 'Shartnomalar'))
const facultyChartOptions = computed(() => horizontalBarOptions(facultyRows.value))

const regionalRows = computed(() => props.regional_demographics.map((r) => ({ label: r.label, count: r.count })))
const regionalSeries = computed(() => barSeries(regionalRows.value, 'Abituriyentlar'))
const regionalChartOptions = computed(() => columnChartOptions(regionalRows.value, { rotateLabels: true }))

// --- To'lov kanali ---
const PROVIDER_LABELS = { cash: 'Xazna', click: 'Click', payme: 'Payme' }
const providerLabel = (p) => PROVIDER_LABELS[p] || p
const channelPct = (ch) => pct(ch.total, financial.value.paid_amount)

// Nisbiy vaqt ("3 kun oldin") — referensdagi "Yangi arizalar (FIFO)"
// jadvalidagi VAQT ustuniga mos.
const timeAgo = (date) => {
    if (!date) return '—'
    const days = Math.floor((Date.now() - new Date(date).getTime()) / 86400000)
    if (days <= 0) return 'Bugun'
    if (days === 1) return 'Kecha'
    return `${days} kun oldin`
}
</script>
