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
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1 bg-brand-600" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">Jami tutorlar</p>
                        <p class="text-2xl font-bold text-gray-900">{{ tutors.length }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1" style="background:#22c55e" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">KPI'ga mos tutorlar ({{ threshold }}%+)</p>
                        <p class="text-2xl font-bold text-gray-900">{{ eligibleCount }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden col-span-2 lg:col-span-1"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1" style="background:#f59e0b" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">O'rtacha ko'rsatkich (barcha tutorlar)</p>
                        <p class="text-2xl font-bold text-gray-900">{{ averageOfAll }}%</p>
                    </div>
                </div>
            </div>

            <!-- Izoh: nima uchun "faqat pul yig'indisi" emas -->
            <div class="bg-white rounded-2xl border border-gray-100 p-4 flex items-start gap-3"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <Icon icon="mdi:information-outline" class="w-5 h-5 text-brand-600 flex-shrink-0 mt-0.5" />
                <p class="text-xs text-gray-500 leading-relaxed">
                    Ko'rsatkich — har bir talabaning shaxsiy to'lov foizi (100% dan oshirilmagan holda)
                    bo'yicha o'rtacha qiymat, oddiy pul yig'indisi emas. Shu sababli bir nechta talaba
                    kontraktni oldindan to'liq to'lab, qolganlari umuman to'lamagan holatda ko'rsatkich
                    haqiqatda past chiqadi — KPI faqat guruh talabalarining ko'pchiligi haqiqatda shu oy
                    uchun deyarli to'liq to'laganda beriladi.
                </p>
            </div>

            <!-- Jadval -->
            <div class="table-grid-wrap">
                <table class="table-grid">
                    <thead>
                    <tr>
                        <th>Tutor</th>
                        <th class="text-center">Guruhlar</th>
                        <th class="text-center">Kontraktli talabalar</th>
                        <th>O'rtacha to'lov %</th>
                        <th class="text-center">KPI</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="!tutors.length">
                        <td colspan="6" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:account-tie-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Hali "Tutor" roli biriktirilgan foydalanuvchi yo'q</p>
                        </td>
                    </tr>
                    <tr v-for="t in tutors" :key="t.id">
                        <td>
                            <p class="text-sm font-semibold text-gray-900">{{ t.full_name }}</p>
                            <p class="text-xs text-gray-400">{{ t.email }}</p>
                        </td>
                        <td class="text-center text-sm text-gray-700">{{ t.groups_count }}</td>
                        <td class="text-center text-sm text-gray-700">{{ t.contract_students_count }}</td>
                        <td>
                            <div v-if="t.average_percent !== null" class="flex items-center gap-2 min-w-[140px]">
                                <div class="flex-1 h-2 rounded-full overflow-hidden bg-gray-100">
                                    <div class="h-full rounded-full transition-all duration-500"
                                         :style="{ width: Math.min(100, t.average_percent) + '%', background: barColor(t) }" />
                                </div>
                                <span class="text-sm font-bold text-gray-700 w-12 text-right">{{ t.average_percent }}%</span>
                            </div>
                            <span v-else class="text-xs text-gray-400">Kontraktli talaba yo'q</span>
                        </td>
                        <td class="text-center">
                            <span v-if="t.kpi_eligible" class="badge-pill badge-success">
                                <Icon icon="mdi:trophy-outline" class="w-3.5 h-3.5" />
                                Mos
                            </span>
                            <span v-else class="badge-pill badge-neutral">—</span>
                        </td>
                        <td>
                            <div class="flex items-center justify-end">
                                <Link :href="route('admin.crm.tutor-kpi.show', t.id)" title="Tafsilot" class="btn-ghost-icon">
                                    <Icon icon="mdi:eye-outline" class="w-4 h-4" />
                                </Link>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
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
    if (t.kpi_eligible) return '#22c55e'
    if (t.average_percent >= 60) return '#f59e0b'
    return '#ef4444'
}
</script>
