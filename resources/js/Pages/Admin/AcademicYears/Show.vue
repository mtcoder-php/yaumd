<template>
    <AppLayout :title="academicYear.name">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('admin.academic-years.index')"
                        class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition flex-shrink-0"
                    >
                        <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-xl font-bold text-gray-900">{{ academicYear.name }}</h1>
                            <span class="badge-pill" :class="academicYear.is_active ? 'badge-success' : 'badge-neutral'">
                                {{ academicYear.is_active ? 'Joriy' : 'Nofaol' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ formatDate(academicYear.start_date) }} — {{ formatDate(academicYear.end_date) }}
                        </p>
                    </div>
                </div>
                <Link :href="route('admin.academic-years.edit', academicYear.id)" class="btn-brand">
                    <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                    Tahrirlash
                </Link>
            </div>

            <!-- Asosiy ma'lumotlar -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <h2 class="text-sm font-bold text-gray-800 mb-4">O'quv yili ma'lumotlari</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Nomi</p>
                        <p class="text-sm font-semibold text-gray-900">{{ academicYear.name || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Status</p>
                        <p class="text-sm font-semibold text-gray-900">{{ academicYear.is_active ? 'Joriy o\'quv yili' : 'Nofaol' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Boshlanish sanasi</p>
                        <p class="text-sm font-semibold text-gray-900">{{ formatDate(academicYear.start_date) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Tugash sanasi</p>
                        <p class="text-sm font-semibold text-gray-900">{{ formatDate(academicYear.end_date) }}</p>
                    </div>
                </div>
            </div>

            <!-- Kichik statistika -->
            <div class="grid grid-cols-2 gap-4 max-w-md">
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1 bg-brand-600" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">Talabalar</p>
                        <p class="text-2xl font-bold text-gray-900">{{ academicYear.students_count ?? 0 }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1" style="background:#22c55e" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">Guruhlar</p>
                        <p class="text-2xl font-bold text-gray-900">{{ academicYear.groups_count ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Guruhlar -->
            <div>
                <h2 class="text-sm font-bold text-gray-800 mb-3">Guruhlar</h2>
                <div class="table-grid-wrap">
                    <table class="table-grid">
                        <thead>
                        <tr>
                            <th>Guruh</th>
                            <th>Yo'nalish</th>
                            <th>Kafedra</th>
                            <th class="text-center">Kurs</th>
                            <th class="text-center">Talabalar</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-if="!groups?.length">
                            <td colspan="6" class="text-center py-8 text-gray-400 text-sm">Guruh qo'shilmagan</td>
                        </tr>
                        <tr v-for="g in groups ?? []" :key="g.id">
                            <td class="text-sm font-medium text-gray-800">{{ g.name }}</td>
                            <td class="text-sm text-gray-600">{{ g.direction?.name_uz || '—' }}</td>
                            <td class="text-sm text-gray-600">{{ g.department?.short_name || g.department?.name_uz || '—' }}</td>
                            <td class="text-sm text-gray-600 text-center">{{ g.course_year }}</td>
                            <td class="text-center">
                                <span class="badge-pill badge-brand">{{ g.students_count ?? 0 }}</span>
                            </td>
                            <td>
                                <span class="badge-pill" :class="g.is_active ? 'badge-success' : 'badge-neutral'">
                                    {{ g.is_active ? 'Faol' : 'Nofaol' }}
                                </span>
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
import { Link } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
    academicYear: { type: Object, required: true },
    groups:       { type: Array,  default: () => [] },
})

const formatDate = (d) => {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric' })
}
</script>
