<template>
    <AppLayout title="Akademik yillar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Akademik yillar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Jami: {{ academicYears.total }} ta o'quv yili</p>
                </div>
                <Link :href="route('admin.academic-years.create')" class="btn-brand">
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    Yangi o'quv yili
                </Link>
            </div>

            <!-- Table -->
            <div class="table-grid-wrap">
                <table class="table-grid">
                    <thead>
                    <tr>
                        <th>O'quv yili</th>
                        <th>Boshlanish</th>
                        <th>Tugash</th>
                        <th class="text-center">Talabalar</th>
                        <th class="text-center">Guruhlar</th>
                        <th>Status</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="!academicYears.data?.length">
                        <td colspan="7" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:calendar-blank-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">O'quv yili topilmadi</p>
                        </td>
                    </tr>
                    <tr v-for="y in academicYears.data ?? []" :key="y.id">

                        <!-- Nomi -->
                        <td>
                            <Link :href="route('admin.academic-years.show', y.id)" class="flex items-center gap-3 group">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white flex-shrink-0 bg-brand-600">
                                    <Icon icon="mdi:calendar-range" class="w-4.5 h-4.5" />
                                </div>
                                <p class="text-sm font-semibold text-gray-900 group-hover:text-brand-600 transition">{{ y.name }}</p>
                            </Link>
                        </td>

                        <!-- Boshlanish -->
                        <td class="text-sm text-gray-600">{{ formatDate(y.start_date) }}</td>

                        <!-- Tugash -->
                        <td class="text-sm text-gray-600">{{ formatDate(y.end_date) }}</td>

                        <!-- Talabalar -->
                        <td class="text-center">
                            <span class="badge-pill badge-brand">{{ y.students_count }}</span>
                        </td>

                        <!-- Guruhlar -->
                        <td class="text-center">
                            <span class="badge-pill badge-success">{{ y.groups_count }}</span>
                        </td>

                        <!-- Status -->
                        <td>
                            <span class="badge-pill" :class="y.is_active ? 'badge-success' : 'badge-neutral'">
                                {{ y.is_active ? 'Joriy' : 'Nofaol' }}
                            </span>
                        </td>

                        <!-- Amallar -->
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="route('admin.academic-years.show', y.id)" title="Ko'rish" class="btn-ghost-icon">
                                    <Icon icon="mdi:eye-outline" class="w-4 h-4" />
                                </Link>
                                <Link :href="route('admin.academic-years.edit', y.id)" title="Tahrirlash" class="btn-ghost-icon">
                                    <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                                </Link>
                                <button @click="confirmDelete(y)" title="O'chirish" class="btn-ghost-icon danger">
                                    <Icon icon="mdi:delete-outline" class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-3">
                    <p class="text-xs text-gray-500">Jami {{ academicYears.total }} ta</p>
                    <div class="flex items-center gap-1.5">
                        <template v-for="link in (academicYears.links ?? [])" :key="link.label">
                            <component
                                :is="link.url ? Link : 'span'"
                                :href="link.url ?? undefined"
                                class="pagination-btn"
                                :class="[link.active ? 'active' : '', !link.url ? 'disabled' : '']"
                            >
                                <ChevronLeftIcon v-if="isPrevLabel(link.label)" class="w-4 h-4" />
                                <ChevronRightIcon v-else-if="isNextLabel(link.label)" class="w-4 h-4" />
                                <span v-else v-html="link.label" />
                            </component>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete modal -->
        <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="deleteTarget = null">
            <div class="bg-white rounded-2xl w-full max-w-sm p-6">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <Icon icon="mdi:delete-outline" class="w-6 h-6 text-red-500" />
                </div>
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">O'quv yilini o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ deleteTarget?.name }}</strong> o'quv yilini o'chirasizmi?
                </p>
                <div class="flex gap-3">
                    <button @click="deleteTarget = null" class="btn-neutral flex-1 justify-center">Bekor qilish</button>
                    <button @click="submitDelete" class="btn-danger-pill flex-1">O'chirish</button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    academicYears: { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
})

const formatDate = (d) => {
    if (!d) return '—'
    return new Date(d).toLocaleDateString('uz-UZ', { day: '2-digit', month: '2-digit', year: 'numeric' })
}

const deleteTarget = ref(null)
const confirmDelete = (y) => { deleteTarget.value = y }
const submitDelete = () => {
    router.delete(route('admin.academic-years.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null },
    })
}

// Laravel'ning standart pagination yorliqlari o'rniga sof strelka
// ikonkalarini ko'rsatish uchun (Foydalanuvchilar sahifasidagi bilan
// bir xil naqsh).
const isPrevLabel = (label) => /Previous|&laquo;|«/i.test(label)
const isNextLabel = (label) => /Next|&raquo;|»/i.test(label)
</script>
