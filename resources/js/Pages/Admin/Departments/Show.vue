<template>
    <AppLayout :title="department.name_uz">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('admin.departments.index')"
                        class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition flex-shrink-0"
                    >
                        <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-xl font-bold text-gray-900">{{ department.name_uz }}</h1>
                            <span class="badge-pill" :class="department.is_active ? 'badge-success' : 'badge-neutral'">
                                {{ department.is_active ? 'Faol' : 'Nofaol' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ department.faculty?.name_uz || '—' }}
                            <span v-if="department.short_name"> · {{ department.short_name }}</span>
                        </p>
                    </div>
                </div>
                <Link :href="route('admin.departments.edit', department.id)" class="btn-brand">
                    <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                    Tahrirlash
                </Link>
            </div>

            <!-- Asosiy ma'lumotlar -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <h2 class="text-sm font-bold text-gray-800 mb-4">Kafedra ma'lumotlari</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Nomi (O'zbek)</p>
                        <p class="text-sm font-semibold text-gray-900">{{ department.name_uz || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Nomi (Rus)</p>
                        <p class="text-sm font-semibold text-gray-900">{{ department.name_ru || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Nomi (Ingliz)</p>
                        <p class="text-sm font-semibold text-gray-900">{{ department.name_en || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Fakultet</p>
                        <Link
                            v-if="department.faculty"
                            :href="route('admin.faculties.show', department.faculty.id)"
                            class="text-sm font-semibold text-brand-600 hover:text-brand-700 transition"
                        >
                            {{ department.faculty.name_uz }}
                        </Link>
                        <p v-else class="text-sm font-semibold text-gray-900">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Kafedra mudiri</p>
                        <p class="text-sm font-semibold text-gray-900">{{ department.head?.full_name || '—' }}</p>
                    </div>
                </div>
            </div>

            <!-- Kichik statistika -->
            <div class="grid grid-cols-2 gap-4 max-w-md">
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1" style="background:#22c55e" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">Yo'nalishlar</p>
                        <p class="text-2xl font-bold text-gray-900">{{ department.directions_count }}</p>
                    </div>
                </div>
            </div>

            <!-- Yo'nalishlar -->
            <div>
                <h2 class="text-sm font-bold text-gray-800 mb-3">Yo'nalishlar</h2>
                <div class="table-grid-wrap">
                    <table class="table-grid">
                        <thead>
                        <tr>
                            <th>Yo'nalish</th>
                            <th>Daraja</th>
                            <th class="text-center">O'quv muddati</th>
                            <th class="text-center">Grant kvota</th>
                            <th class="text-center">Kontrakt kvota</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-if="!department.directions?.length">
                            <td colspan="6" class="text-center py-8 text-gray-400 text-sm">Yo'nalish qo'shilmagan</td>
                        </tr>
                        <tr v-for="dir in department.directions ?? []" :key="dir.id">
                            <td class="text-sm font-medium text-gray-800">{{ dir.name_uz }}</td>
                            <td class="text-sm text-gray-600">{{ degreeLabel(dir.degree) }}</td>
                            <td class="text-sm text-gray-600 text-center">{{ dir.duration_years }} yil</td>
                            <td class="text-sm text-gray-600 text-center">{{ dir.quota_grant ?? 0 }}</td>
                            <td class="text-sm text-gray-600 text-center">{{ dir.quota_contract ?? 0 }}</td>
                            <td>
                                <span class="badge-pill" :class="dir.is_active ? 'badge-success' : 'badge-neutral'">
                                    {{ dir.is_active ? 'Faol' : 'Nofaol' }}
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
    department: { type: Object, required: true },
})

const DEGREE_LABELS = { bachelor: 'Bakalavr', master: 'Magistr' }
const degreeLabel = (v) => DEGREE_LABELS[v] || v || '—'
</script>
