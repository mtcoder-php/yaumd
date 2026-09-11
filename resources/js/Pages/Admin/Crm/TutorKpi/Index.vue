<template>
    <AppLayout title="Tutor KPI">
        <div class="space-y-5">

            <!-- Header -->
            <div>
                <h1 class="text-xl font-bold text-gray-900">Tutor KPI</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    Har bir tutorga biriktirilgan guruh/talabalarning shu oy uchun mo'ljallangan
                    kontrakt to'lovini necha foizga to'lagani — {{ threshold }}%+ bo'lsa KPI/bonus uchun tavsiya etiladi.
                </p>
            </div>

            <!-- Stat kartalar -->
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
                <div class="bg-white rounded-xl border border-gray-100 p-4"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <p class="text-2xl font-bold text-gray-900">{{ tutors.length }}</p>
                    <p class="text-xs text-gray-400 mt-1">Jami tutorlar</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <p class="text-2xl font-bold text-green-700">{{ eligibleCount }}</p>
                    <p class="text-xs text-gray-400 mt-1">KPI'ga mos tutorlar ({{ threshold }}%+)</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-100 p-4 col-span-2 lg:col-span-1"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <p class="text-2xl font-bold text-gray-900">{{ averageOfAll }}%</p>
                    <p class="text-xs text-gray-400 mt-1">O'rtacha ko'rsatkich (barcha tutorlar)</p>
                </div>
            </div>

            <!-- Izoh: nima uchun "faqat pul yig'indisi" emas -->
            <div class="bg-white rounded-2xl border border-gray-100 p-4 flex items-start gap-3"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <Icon icon="mdi:information-outline" class="w-5 h-5 text-[#0f3460] flex-shrink-0 mt-0.5" />
                <p class="text-xs text-gray-500 leading-relaxed">
                    Ko'rsatkich — har bir talabaning shaxsiy to'lov foizi (100% dan oshirilmagan holda)
                    bo'yicha o'rtacha qiymat, oddiy pul yig'indisi emas. Shu sababli bir nechta talaba
                    kontraktni oldindan to'liq to'lab, qolganlari umuman to'lamagan holatda ko'rsatkich
                    haqiqatda past chiqadi — KPI faqat guruh talabalarining ko'pchiligi haqiqatda shu oy
                    uchun deyarli to'liq to'laganda beriladi.
                </p>
            </div>

            <!-- Jadval -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Tutor</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Guruhlar</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Kontraktli talabalar</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">O'rtacha to'lov %</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">KPI</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                        <tr v-if="!tutors.length">
                            <td colspan="6" class="text-center py-16 text-gray-400">
                                <Icon icon="mdi:account-tie-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                                <p class="text-sm">Hali "Tutor" roli biriktirilgan foydalanuvchi yo'q</p>
                            </td>
                        </tr>
                        <tr v-for="t in tutors" :key="t.id" class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3">
                                <p class="text-sm font-semibold text-gray-900">{{ t.full_name }}</p>
                                <p class="text-xs text-gray-400">{{ t.email }}</p>
                            </td>
                            <td class="px-4 py-3 text-center text-sm text-gray-700">{{ t.groups_count }}</td>
                            <td class="px-4 py-3 text-center text-sm text-gray-700">{{ t.contract_students_count }}</td>
                            <td class="px-4 py-3">
                                <div v-if="t.average_percent !== null" class="flex items-center gap-2 min-w-[140px]">
                                    <div class="flex-1 h-2 rounded-full overflow-hidden bg-gray-100">
                                        <div class="h-full rounded-full transition-all duration-500"
                                             :style="{ width: Math.min(100, t.average_percent) + '%', background: barColor(t) }" />
                                    </div>
                                    <span class="text-sm font-bold text-gray-700 w-12 text-right">{{ t.average_percent }}%</span>
                                </div>
                                <span v-else class="text-xs text-gray-400">Kontraktli talaba yo'q</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span v-if="t.kpi_eligible"
                                      class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700">
                                    <Icon icon="mdi:trophy-outline" class="w-3.5 h-3.5" />
                                    Mos
                                </span>
                                <span v-else
                                      class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-500">
                                    —
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <Link :href="route('admin.crm.tutor-kpi.show', t.id)"
                                      class="text-xs font-medium text-[#0f3460] hover:text-[#533483] inline-flex items-center gap-1 whitespace-nowrap">
                                    <Icon icon="mdi:eye-outline" class="w-3.5 h-3.5" />
                                    Tafsilot
                                </Link>
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

const props = defineProps({
    tutors: { type: Array, default: () => [] },
})

// Backenddagi TutorKpiService::KPI_THRESHOLD_PERCENT bilan mos (hozircha
// bitta joyda — server — saqlanadi, bu yerda faqat matn uchun ko'rsatiladi).
const threshold = 90

const eligibleCount = computed(() => props.tutors.filter((t) => t.kpi_eligible).length)

const averageOfAll = computed(() => {
    const withPercent = props.tutors.filter((t) => t.average_percent !== null)
    if (!withPercent.length) return 0
    const sum = withPercent.reduce((acc, t) => acc + t.average_percent, 0)
    return Math.round((sum / withPercent.length) * 10) / 10
})

const barColor = (t) => {
    if (t.kpi_eligible) return 'linear-gradient(90deg,#15803d,#22c55e)'
    if (t.average_percent >= 60) return 'linear-gradient(90deg,#b45309,#f59e0b)'
    return 'linear-gradient(90deg,#b91c1c,#ef4444)'
}
</script>
