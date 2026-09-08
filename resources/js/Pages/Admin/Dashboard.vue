<template>
    <AppLayout title="Dashboard">
        <div class="space-y-6">

            <!-- Stat kartalar -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div
                    v-for="card in statCards"
                    :key="card.label"
                    class="bg-white rounded-2xl border border-gray-100 p-5 flex items-start gap-4"
                    style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)"
                >
                    <div
                        class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                        :style="{ background: card.bg }"
                    >
                        <Icon :icon="card.icon" class="w-5 h-5 text-white" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">{{ card.label }}</p>
                        <p class="text-2xl font-bold text-gray-900">{{ card.value }}</p>
                        <p v-if="card.sub" class="text-xs mt-0.5" :style="{ color: card.subColor || '#9ca3af' }">
                            {{ card.sub }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Grafik + Status bo'yicha -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                <!-- Haftalik arizalar grafigi -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-5"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <h2 class="text-sm font-bold text-gray-700 mb-2">Oxirgi 7 kunlik arizalar</h2>
                    <apexchart type="area" height="220" :options="weeklyChartOptions" :series="weeklySeries" />
                </div>

                <!-- Status bo'yicha -->
                <div class="bg-white rounded-2xl border border-gray-100 p-5"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <h2 class="text-sm font-bold text-gray-700 mb-2">Status bo'yicha</h2>
                    <apexchart type="bar" :height="statusChartHeight" :options="statusChartOptions" :series="statusSeries" />
                </div>
            </div>

            <!-- Ta'lim turi + Tizim holati -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                <!-- Ta'lim turi bo'yicha -->
                <div class="bg-white rounded-2xl border border-gray-100 p-5"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <h2 class="text-sm font-bold text-gray-700 mb-2">Ta'lim turi</h2>
                    <apexchart type="bar" :height="educationChartHeight" :options="educationChartOptions" :series="educationSeries" />
                </div>

                <!-- Tizim holati -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-5"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <h2 class="text-sm font-bold text-gray-700 mb-4">Tizim holati</h2>
                    <div class="grid grid-cols-2 gap-3">
                        <div
                            v-for="item in systemStatus"
                            :key="item.label"
                            class="flex items-center justify-between p-3 rounded-xl border"
                            :class="item.ok ? 'border-green-100 bg-green-50' : 'border-red-100 bg-red-50'"
                        >
                            <div class="flex items-center gap-2">
                                <Icon
                                    :icon="item.ok ? 'mdi:check-circle-outline' : 'mdi:close-circle-outline'"
                                    class="w-4 h-4"
                                    :class="item.ok ? 'text-green-600' : 'text-red-500'"
                                />
                                <span class="text-xs text-gray-700 font-medium">{{ item.label }}</span>
                            </div>
                            <span
                                class="text-xs px-2 py-0.5 rounded-full font-medium"
                                :class="item.ok ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600'"
                            >
                                {{ item.ok ? 'OK' : 'Xato' }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- So'nggi arizalar -->
            <div class="bg-white rounded-2xl border border-gray-100 p-5"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-gray-700">So'nggi arizalar</h2>
                    <Link
                        :href="route('admin.applicants.index')"
                        class="text-xs font-semibold flex items-center gap-1 transition"
                        style="color: #0f3460"
                    >
                        Barchasi
                        <Icon icon="mdi:arrow-right" class="w-3.5 h-3.5" />
                    </Link>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left pb-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Ariza №</th>
                            <th class="text-left pb-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">F.I.Sh</th>
                            <th class="text-left pb-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Yo'nalish</th>
                            <th class="text-left pb-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="text-left pb-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Sana</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                        <tr v-if="!recentApplicants?.length">
                            <td colspan="5" class="py-8 text-center text-xs text-gray-400">
                                Hali ariza yo'q
                            </td>
                        </tr>
                        <tr v-for="a in recentApplicants" :key="a.id" class="hover:bg-gray-50 transition">
                            <td class="py-3 pr-4">
                                <Link
                                    :href="route('admin.applicants.show', a.id)"
                                    class="text-xs font-mono font-semibold hover:underline"
                                    style="color: #0f3460"
                                >
                                    {{ a.application_number }}
                                </Link>
                            </td>
                            <td class="py-3 pr-4 text-sm text-gray-800">
                                {{ a.last_name }} {{ a.first_name }}
                            </td>
                            <td class="py-3 pr-4 text-xs text-gray-500">
                                {{ a.direction?.name_uz || '—' }}
                            </td>
                            <td class="py-3 pr-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                          :class="statusBadge(a.status)">
                                        {{ statusLabel(a.status) }}
                                    </span>
                            </td>
                            <td class="py-3 text-xs text-gray-400">
                                {{ formatDate(a.created_at) }}
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import { barSeries, horizontalBarOptions, areaChartOptions } from '@/lib/charts'

const props = defineProps({
    stats:              { type: Object, default: () => ({}) },
    applicant_by_status:{ type: Object, default: () => ({}) },
    applicant_by_type:  { type: Object, default: () => ({}) },
    weekly_applicants:  { type: Array,  default: () => [] },
    recent_applicants:  { type: Array,  default: () => [] },
})

const applicantByStatus = computed(() => props.applicant_by_status || {})
const applicantByType   = computed(() => props.applicant_by_type   || {})
const recentApplicants  = computed(() => props.recent_applicants   || [])

// Stat kartalar
const statCards = computed(() => [
    {
        label: 'Jami arizalar',
        value: props.stats.applicants_total ?? 0,
        icon:  'mdi:file-document-multiple-outline',
        bg:    'linear-gradient(135deg, #0f3460, #533483)',
        sub:   `Bugun: +${props.stats.applicants_today ?? 0}`,
        subColor: '#22c55e',
    },
    {
        label: 'Yangi arizalar',
        value: props.stats.applicants_new ?? 0,
        icon:  'mdi:file-plus-outline',
        bg:    'linear-gradient(135deg, #3b82f6, #1d4ed8)',
        sub:   "Ko'rib chiqilmagan",
        subColor: '#f97316',
    },
    {
        label: 'Ro\'yxatga olingan',
        value: props.stats.enrolled ?? 0,
        icon:  'mdi:school-outline',
        bg:    'linear-gradient(135deg, #22c55e, #16a34a)',
    },
    {
        label: 'Talabalar',
        value: props.stats.students ?? 0,
        icon:  'mdi:account-group-outline',
        bg:    'linear-gradient(135deg, #8b5cf6, #6d28d9)',
    },
])

// Haftalik grafik
const days = ['Yak', 'Du', 'Se', 'Ch', 'Pa', 'Ju', 'Sh']
const weeklyData = computed(() => {
    const result = []
    for (let i = 6; i >= 0; i--) {
        const date = new Date()
        date.setDate(date.getDate() - i)
        const dateStr = date.toISOString().split('T')[0]
        const found = props.weekly_applicants.find(w => w.date === dateStr)
        result.push({
            label: days[date.getDay()],
            count: found?.count || 0,
        })
    }
    return result
})

// Haftalik grafik uchun ApexCharts sozlamalari — bitta seriya, brend
// gradienti (Reports.vue bilan bir xil umumiy modul: @/lib/charts).
const weeklySeries = computed(() => [{ name: 'Arizalar', data: weeklyData.value.map((d) => d.count) }])
const weeklyChartOptions = computed(() => areaChartOptions(weeklyData.value.map((d) => d.label)))

// Statuslar
const statusList = [
    { value: 'new',        label: 'Yangi' },
    { value: 'accepted',   label: 'Qabul qilindi' },
    { value: 'interview',  label: 'Suhbat' },
    { value: 'tested',     label: 'Test' },
    { value: 'contracted', label: 'Kontrakt' },
    { value: 'enrolled',   label: "Ro'yxatga olindi" },
    { value: 'rejected',   label: 'Rad etildi' },
]

// Status bo'yicha taqsimot — bir nechta kategoriya bo'yicha miqdorni
// solishtirish, shuning uchun ko'p rangli emas, bitta brend rangida
// (dataviz: "sequential is the safe default" — categorical faqat seriyalar
// o'zi mavzu bo'lganda kerak, bu yerda esa faqat bitta o'lchov bor).
const statusRows = computed(() => statusList.map((s) => ({ label: s.label, count: applicantByStatus.value[s.value] || 0 })))
const statusSeries = computed(() => barSeries(statusRows.value, 'Arizalar'))
const statusChartOptions = computed(() => horizontalBarOptions(statusRows.value))
const statusChartHeight = computed(() => Math.max(200, statusRows.value.length * 34))

const statusBadge = (s) => {
    const badges = {
        new:        'bg-blue-50 text-blue-700',
        accepted:   'bg-green-50 text-green-700',
        interview:  'bg-yellow-50 text-yellow-700',
        tested:     'bg-purple-50 text-purple-700',
        contracted: 'bg-indigo-50 text-indigo-700',
        enrolled:   'bg-teal-50 text-teal-700',
        rejected:   'bg-red-50 text-red-700',
    }
    return badges[s] || 'bg-gray-50 text-gray-600'
}

const statusLabel = (s) => statusList.find(x => x.value === s)?.label || s

// Ta'lim turlari
const educationTypes = [
    { value: 'bachelor', label: 'Bakalavr' },
    { value: 'master',   label: 'Magistr' },
    { value: 'transfer', label: 'Transfer' },
    { value: 'second',   label: 'Ikkinchi-mutaxassislik' },
]

const educationRows = computed(() => educationTypes.map((t) => ({ label: t.label, count: applicantByType.value[t.value] || 0 })))
const educationSeries = computed(() => barSeries(educationRows.value, 'Arizalar'))
const educationChartOptions = computed(() => horizontalBarOptions(educationRows.value))
const educationChartHeight = computed(() => Math.max(160, educationRows.value.length * 42))

// Tizim holati
const systemStatus = [
    { label: "Ma'lumotlar bazasi", ok: true },
    { label: 'Auth tizimi',        ok: true },
    { label: 'RBAC / Rollar',      ok: true },
    { label: 'Fayl saqlash',       ok: true },
    { label: 'SMS Gateway',        ok: false },
    { label: 'HEMIS integratsiya', ok: false },
]

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('uz-UZ', {
        day: '2-digit', month: '2-digit', year: 'numeric'
    })
}
</script>
