<template>
    <AppLayout title="Fakultetlar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Fakultetlar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Jami: {{ faculties.total }} ta fakultet</p>
                </div>
                <Link :href="route('admin.faculties.create')" class="btn-brand">
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    Yangi fakultet
                </Link>
            </div>

            <!-- Table — to'liq to'r (grid) chegarali dizayn (Foydalanuvchilar
                 sahifasida o'rnatilgan .table-grid* naqshi). -->
            <div class="table-grid-wrap">
                <table class="table-grid">
                    <thead>
                    <tr>
                        <th>Fakultet</th>
                        <th>Qisqa nomi</th>
                        <th>Dekan</th>
                        <th class="text-center">Kafedralar</th>
                        <th class="text-center">Yo'nalishlar</th>
                        <th>Status</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="!faculties.data?.length">
                        <td colspan="7" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:school-off-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Fakultet topilmadi</p>
                        </td>
                    </tr>
                    <tr v-for="f in faculties.data ?? []" :key="f.id">

                        <!-- Fakultet -->
                        <td>
                            <Link :href="route('admin.faculties.show', f.id)" class="flex items-center gap-3 group">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white text-xs flex-shrink-0 bg-brand-600">
                                    {{ f.short_name?.substring(0, 2)?.toUpperCase() || 'F' }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 group-hover:text-brand-600 transition">{{ f.name_uz }}</p>
                                    <p class="text-xs text-gray-400">{{ f.name_ru }}</p>
                                </div>
                            </Link>
                        </td>

                        <!-- Qisqa nomi -->
                        <td>
                            <span class="text-sm font-mono font-semibold text-gray-700">{{ f.short_name }}</span>
                        </td>

                        <!-- Dekan -->
                        <td class="text-sm text-gray-600">
                            {{ f.dean?.full_name || '—' }}
                        </td>

                        <!-- Kafedralar -->
                        <td class="text-center">
                            <span class="badge-pill badge-brand">{{ f.departments_count }}</span>
                        </td>

                        <!-- Yo'nalishlar -->
                        <td class="text-center">
                            <span class="badge-pill badge-success">{{ f.directions_count }}</span>
                        </td>

                        <!-- Status -->
                        <td>
                            <span class="badge-pill" :class="f.is_active ? 'badge-success' : 'badge-neutral'">
                                {{ f.is_active ? 'Faol' : 'Nofaol' }}
                            </span>
                        </td>

                        <!-- Amallar -->
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="route('admin.faculties.show', f.id)" title="Ko'rish" class="btn-ghost-icon">
                                    <Icon icon="mdi:eye-outline" class="w-4 h-4" />
                                </Link>
                                <Link :href="route('admin.faculties.edit', f.id)" title="Tahrirlash" class="btn-ghost-icon">
                                    <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                                </Link>
                                <button @click="confirmDelete(f)" title="O'chirish" class="btn-ghost-icon danger">
                                    <Icon icon="mdi:delete-outline" class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-3">
                    <p class="text-xs text-gray-500">Jami {{ faculties.total }} ta</p>
                    <div class="flex items-center gap-1.5">
                        <template v-for="link in (faculties.links ?? [])" :key="link.label">
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
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Fakultetni o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ deleteTarget?.name_uz }}</strong> fakultetini o'chirasizmi?
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
    faculties: { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
})

const deleteTarget = ref(null)
const confirmDelete = (f) => { deleteTarget.value = f }
const submitDelete = () => {
    router.delete(route('admin.faculties.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null },
    })
}

// Laravel'ning standart pagination yorliqlari o'rniga sof strelka
// ikonkalarini ko'rsatish uchun (Foydalanuvchilar sahifasidagi bilan
// bir xil naqsh).
const isPrevLabel = (label) => /Previous|&laquo;|«/i.test(label)
const isNextLabel = (label) => /Next|&raquo;|»/i.test(label)
</script>
