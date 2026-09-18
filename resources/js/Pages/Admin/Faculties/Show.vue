<template>
    <AppLayout :title="faculty.name_uz">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('admin.faculties.index')"
                        class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition flex-shrink-0"
                    >
                        <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-xl font-bold text-gray-900">{{ faculty.name_uz }}</h1>
                            <span class="badge-pill" :class="faculty.is_active ? 'badge-success' : 'badge-neutral'">
                                {{ faculty.is_active ? 'Faol' : 'Nofaol' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-0.5">{{ faculty.short_name }}</p>
                    </div>
                </div>
                <Link :href="route('admin.faculties.edit', faculty.id)" class="btn-brand">
                    <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                    Tahrirlash
                </Link>
            </div>

            <!-- Asosiy ma'lumotlar -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <h2 class="text-sm font-bold text-gray-800 mb-4">Fakultet ma'lumotlari</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Nomi (O'zbek)</p>
                        <p class="text-sm font-semibold text-gray-900">{{ faculty.name_uz || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Nomi (Rus)</p>
                        <p class="text-sm font-semibold text-gray-900">{{ faculty.name_ru || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Nomi (Ingliz)</p>
                        <p class="text-sm font-semibold text-gray-900">{{ faculty.name_en || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Dekan</p>
                        <p class="text-sm font-semibold text-gray-900">{{ faculty.dean?.full_name || '—' }}</p>
                    </div>
                    <div class="sm:col-span-2" v-if="faculty.description_uz">
                        <p class="text-xs text-gray-400 mb-1">Tavsif</p>
                        <p class="text-sm text-gray-700 leading-relaxed">{{ faculty.description_uz }}</p>
                    </div>
                </div>
            </div>

            <!-- Kichik statistikalar -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1 bg-brand-600" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">Kafedralar</p>
                        <p class="text-2xl font-bold text-gray-900">{{ faculty.departments_count }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1" style="background:#22c55e" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">Yo'nalishlar</p>
                        <p class="text-2xl font-bold text-gray-900">{{ faculty.directions_count }}</p>
                    </div>
                </div>
            </div>

            <!-- Kafedralar -->
            <div>
                <h2 class="text-sm font-bold text-gray-800 mb-3">Kafedralar</h2>
                <div class="table-grid-wrap">
                    <table class="table-grid">
                        <thead>
                        <tr>
                            <th>Kafedra</th>
                            <th>Qisqa nomi</th>
                            <th>Mudir</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr v-if="!faculty.departments?.length">
                            <td colspan="4" class="text-center py-8 text-gray-400 text-sm">Kafedra qo'shilmagan</td>
                        </tr>
                        <tr v-for="d in faculty.departments ?? []" :key="d.id">
                            <td class="text-sm font-medium text-gray-800">{{ d.name_uz }}</td>
                            <td class="text-sm font-mono text-gray-600">{{ d.short_name || '—' }}</td>
                            <td class="text-sm text-gray-600">{{ d.head?.full_name || '—' }}</td>
                            <td>
                                <span class="badge-pill" :class="d.is_active ? 'badge-success' : 'badge-neutral'">
                                    {{ d.is_active ? 'Faol' : 'Nofaol' }}
                                </span>
                            </td>
                        </tr>
                        </tbody>
                    </table>
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
                        <tr v-if="!faculty.directions?.length">
                            <td colspan="6" class="text-center py-8 text-gray-400 text-sm">Yo'nalish qo'shilmagan</td>
                        </tr>
                        <tr v-for="dir in faculty.directions ?? []" :key="dir.id">
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
    faculty: { type: Object, required: true },
})

const DEGREE_LABELS = { bachelor: 'Bakalavr', master: 'Magistr' }
const degreeLabel = (v) => DEGREE_LABELS[v] || v || '—'
</script>
