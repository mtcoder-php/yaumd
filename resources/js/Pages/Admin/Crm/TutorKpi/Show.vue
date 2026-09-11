<template>
    <AppLayout :title="isSelf ? 'Mening KPI\'m' : 'Tutor KPI — tafsilot'">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <Link v-if="!isSelf" :href="route('admin.crm.tutor-kpi.index')"
                          class="text-xs text-gray-400 hover:text-gray-600 inline-flex items-center gap-1 mb-1">
                        <Icon icon="mdi:arrow-left" class="w-3.5 h-3.5" />
                        Barcha tutorlar
                    </Link>
                    <h1 class="text-xl font-bold text-gray-900">
                        {{ isSelf ? 'Mening KPI\'m' : report.tutor.full_name }}
                    </h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ isSelf ? "Guruhlaringiz talabalarining shu oy uchun kontrakt to'lovi bo'yicha ko'rsatkichi" : report.tutor.email }}
                    </p>
                </div>
            </div>

            <!-- Umumiy ko'rsatkich -->
            <div class="bg-white rounded-2xl border border-gray-100 p-5"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="flex items-center justify-between flex-wrap gap-3 mb-3">
                    <div>
                        <h2 class="text-sm font-bold text-gray-700">Umumiy ko'rsatkich</h2>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ report.summary.contract_students_count }} ta kontraktli talabaning shaxsiy
                            foizlari o'rtachasi ({{ report.groups.length }} ta guruh)
                        </p>
                    </div>
                    <span v-if="report.summary.kpi_eligible"
                          class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-sm font-semibold bg-green-50 text-green-700">
                        <Icon icon="mdi:trophy-outline" class="w-4 h-4" />
                        KPI'ga mos ({{ threshold }}%+)
                    </span>
                    <span v-else-if="report.summary.average_percent !== null"
                          class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-medium bg-gray-100 text-gray-500">
                        Hozircha KPI'ga mos emas
                    </span>
                </div>

                <div v-if="report.summary.average_percent !== null" class="h-3 rounded-full overflow-hidden bg-gray-100">
                    <div class="h-full rounded-full transition-all duration-500"
                         :style="{ width: Math.min(100, report.summary.average_percent) + '%', background: barColor(report.summary) }" />
                </div>
                <p v-if="report.summary.average_percent !== null" class="text-right text-2xl font-bold text-gray-900 mt-2">
                    {{ report.summary.average_percent }}%
                </p>
                <p v-else class="text-sm text-gray-400 py-4 text-center">
                    Hech qaysi guruhda kontrakt asosida o'qiydigan talaba topilmadi
                </p>
            </div>

            <!-- Har bir guruh -->
            <div v-for="group in report.groups" :key="group.id"
                 class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <div class="p-4 border-b border-gray-100 flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">{{ group.name }}</h3>
                        <p class="text-xs text-gray-400 mt-0.5">
                            {{ group.direction || "Yo'nalish ko'rsatilmagan" }} ·
                            {{ group.academic_year || "O'quv yili ko'rsatilmagan" }} ·
                            {{ group.students_count }} talaba ·
                            {{ group.months_elapsed }}/{{ installmentMonths }}-oy
                        </p>
                    </div>
                    <div class="text-right" v-if="group.summary.average_percent !== null">
                        <p class="text-lg font-bold" :style="{ color: group.summary.kpi_eligible ? '#15803d' : '#111827' }">
                            {{ group.summary.average_percent }}%
                        </p>
                        <p class="text-xs text-gray-400">{{ group.summary.contract_students_count }} kontraktli talaba</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Talaba</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Kontrakt summasi</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Shu kungacha kerak</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">To'langan</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-400 uppercase tracking-wider">Foiz</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                        <tr v-for="s in group.students" :key="s.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-2.5 text-sm text-gray-800">{{ s.full_name }}</td>
                            <template v-if="s.has_contract">
                                <td class="px-4 py-2.5 text-right text-sm text-gray-600">{{ formatAmount(s.contract_amount) }}</td>
                                <td class="px-4 py-2.5 text-right text-sm text-gray-600">{{ formatAmount(s.due_to_date) }}</td>
                                <td class="px-4 py-2.5 text-right text-sm text-gray-600">{{ formatAmount(s.paid_to_date) }}</td>
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-2 min-w-[120px]">
                                        <div class="flex-1 h-1.5 rounded-full overflow-hidden bg-gray-100">
                                            <div class="h-full rounded-full"
                                                 :style="{ width: Math.min(100, s.percent) + '%', background: percentColor(s.percent) }" />
                                        </div>
                                        <span class="text-xs font-semibold text-gray-600 w-10 text-right">{{ s.percent }}%</span>
                                    </div>
                                </td>
                            </template>
                            <template v-else>
                                <td colspan="4" class="px-4 py-2.5 text-xs text-gray-400 italic">Kontrakt asosida o'qimaydi (grant)</td>
                            </template>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div v-if="!report.groups.length" class="bg-white rounded-2xl border border-gray-100 p-10 text-center text-gray-400"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <Icon icon="mdi:account-group-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                <p class="text-sm">Hali biriktirilgan guruh yo'q</p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
    report: { type: Object, required: true },
    isSelf: { type: Boolean, default: false },
})

// Backenddagi TutorKpiService konstantalari bilan mos (faqat matn/UI uchun).
const threshold = 90
const installmentMonths = 8

const formatAmount = (v) => {
    if (!v) return '0 so\'m'
    return new Intl.NumberFormat('uz-UZ').format(v) + " so'm"
}

const barColor = (summary) => {
    if (summary.kpi_eligible) return 'linear-gradient(90deg,#15803d,#22c55e)'
    if ((summary.average_percent ?? 0) >= 60) return 'linear-gradient(90deg,#b45309,#f59e0b)'
    return 'linear-gradient(90deg,#b91c1c,#ef4444)'
}

const percentColor = (p) => {
    if (p >= threshold) return '#22c55e'
    if (p >= 60) return '#f59e0b'
    return '#ef4444'
}
</script>
