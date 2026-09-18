<template>
    <AppLayout :title="direction.name_uz">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('admin.directions.index')"
                        class="w-9 h-9 rounded-xl border border-gray-200 flex items-center justify-center hover:bg-gray-50 transition flex-shrink-0"
                    >
                        <Icon icon="mdi:arrow-left" class="w-5 h-5 text-gray-600" />
                    </Link>
                    <div>
                        <div class="flex items-center gap-2.5">
                            <h1 class="text-xl font-bold text-gray-900">{{ direction.name_uz }}</h1>
                            <span class="badge-pill" :class="direction.degree === 'bachelor' ? 'badge-brand' : 'badge-warning'">
                                {{ direction.degree === 'bachelor' ? 'Bakalavr' : 'Magistr' }}
                            </span>
                            <span class="badge-pill" :class="direction.is_active ? 'badge-success' : 'badge-neutral'">
                                {{ direction.is_active ? 'Faol' : 'Nofaol' }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-0.5">
                            {{ direction.faculty?.name_uz || '—' }}
                            <span v-if="direction.department"> · {{ direction.department.name_uz }}</span>
                            <span v-if="direction.hemis_code"> · HEMIS: {{ direction.hemis_code }}</span>
                        </p>
                    </div>
                </div>
                <Link :href="route('admin.directions.edit', direction.id)" class="btn-brand">
                    <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                    Tahrirlash
                </Link>
            </div>

            <!-- Asosiy ma'lumotlar -->
            <div class="bg-white rounded-2xl border border-gray-100 p-6"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <h2 class="text-sm font-bold text-gray-800 mb-4">Yo'nalish ma'lumotlari</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Nomi (O'zbek)</p>
                        <p class="text-sm font-semibold text-gray-900">{{ direction.name_uz || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Nomi (Rus)</p>
                        <p class="text-sm font-semibold text-gray-900">{{ direction.name_ru || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Nomi (Ingliz)</p>
                        <p class="text-sm font-semibold text-gray-900">{{ direction.name_en || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">HEMIS kodi</p>
                        <p class="text-sm font-semibold text-gray-900">{{ direction.hemis_code || '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Fakultet</p>
                        <Link
                            v-if="direction.faculty"
                            :href="route('admin.faculties.show', direction.faculty.id)"
                            class="text-sm font-semibold text-brand-600 hover:text-brand-700 transition"
                        >
                            {{ direction.faculty.name_uz }}
                        </Link>
                        <p v-else class="text-sm font-semibold text-gray-900">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Kafedra</p>
                        <Link
                            v-if="direction.department"
                            :href="route('admin.departments.show', direction.department.id)"
                            class="text-sm font-semibold text-brand-600 hover:text-brand-700 transition"
                        >
                            {{ direction.department.name_uz }}
                        </Link>
                        <p v-else class="text-sm font-semibold text-gray-900">—</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">O'qish muddati</p>
                        <p class="text-sm font-semibold text-gray-900">{{ direction.duration_years }} yil</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Yillik to'lov</p>
                        <p class="text-sm font-semibold text-gray-900">{{ formatAmount(direction.annual_fee) }}</p>
                    </div>
                </div>
            </div>

            <!-- Kichik statistika -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1" style="background:#22c55e" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">Grant kvota</p>
                        <p class="text-2xl font-bold text-gray-900">{{ direction.quota_grant ?? 0 }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1 bg-brand-600" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">Kontrakt kvota</p>
                        <p class="text-2xl font-bold text-gray-900">{{ direction.quota_contract ?? 0 }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1" style="background:#f59e0b" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">Talabalar</p>
                        <p class="text-2xl font-bold text-gray-900">{{ direction.students_count ?? 0 }}</p>
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                     style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="h-1" style="background:#6366f1" />
                    <div class="p-4">
                        <p class="text-xs font-medium text-gray-500 mb-1">Abituriyentlar</p>
                        <p class="text-2xl font-bold text-gray-900">{{ direction.applicants_count ?? 0 }}</p>
                    </div>
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
    direction: { type: Object, required: true },
})

const formatAmount = (amount) => {
    if (!amount) return '—'
    return new Intl.NumberFormat('uz-UZ').format(amount) + " so'm"
}
</script>
