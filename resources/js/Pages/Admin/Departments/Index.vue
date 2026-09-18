<template>
    <AppLayout title="Kafedralar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Kafedralar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Jami: {{ departments.total }} ta kafedra</p>
                </div>
                <Link :href="route('admin.departments.create')" class="btn-brand">
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    Yangi kafedra
                </Link>
            </div>

            <!-- Table — to'liq to'r (grid) chegarali dizayn (Foydalanuvchilar
                 sahifasida o'rnatilgan .table-grid* naqshi). -->
            <div class="table-grid-wrap">
                <table class="table-grid">
                    <thead>
                    <tr>
                        <th>Kafedra</th>
                        <th>Fakultet</th>
                        <th>Mudiri</th>
                        <th class="text-center">Yo'nalishlar</th>
                        <th>Status</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="!departments.data?.length">
                        <td colspan="6" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:office-building-off-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Kafedra topilmadi</p>
                        </td>
                    </tr>
                    <tr v-for="d in departments.data ?? []" :key="d.id">

                        <!-- Kafedra -->
                        <td>
                            <Link :href="route('admin.departments.show', d.id)" class="flex items-center gap-3 group">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-white text-xs flex-shrink-0 bg-brand-600">
                                    {{ d.short_name?.substring(0, 2)?.toUpperCase() || 'K' }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900 group-hover:text-brand-600 transition">{{ d.name_uz }}</p>
                                    <p class="text-xs text-gray-400">{{ d.name_ru }}</p>
                                </div>
                            </Link>
                        </td>

                        <!-- Fakultet -->
                        <td class="text-sm text-gray-600">
                            {{ d.faculty?.short_name || d.faculty?.name_uz || '—' }}
                        </td>

                        <!-- Mudiri -->
                        <td class="text-sm text-gray-600">
                            {{ d.head?.full_name || '—' }}
                        </td>

                        <!-- Yo'nalishlar -->
                        <td class="text-center">
                            <span class="badge-pill badge-success">{{ d.directions_count }}</span>
                        </td>

                        <!-- Status -->
                        <td>
                            <span class="badge-pill" :class="d.is_active ? 'badge-success' : 'badge-neutral'">
                                {{ d.is_active ? 'Faol' : 'Nofaol' }}
                            </span>
                        </td>

                        <!-- Amallar -->
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="route('admin.departments.show', d.id)" title="Ko'rish" class="btn-ghost-icon">
                                    <Icon icon="mdi:eye-outline" class="w-4 h-4" />
                                </Link>
                                <Link :href="route('admin.departments.edit', d.id)" title="Tahrirlash" class="btn-ghost-icon">
                                    <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                                </Link>
                                <button @click="confirmDelete(d)" title="O'chirish" class="btn-ghost-icon danger">
                                    <Icon icon="mdi:delete-outline" class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-3">
                    <p class="text-xs text-gray-500">Jami {{ departments.total }} ta</p>
                    <div class="flex items-center gap-1.5">
                        <template v-for="link in (departments.links ?? [])" :key="link.label">
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
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Kafedrani o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ deleteTarget?.name_uz }}</strong> kafedrasini o'chirasizmi?
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
    departments: { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
})

const deleteTarget = ref(null)
const confirmDelete = (d) => { deleteTarget.value = d }
const submitDelete = () => {
    router.delete(route('admin.departments.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null },
    })
}

// Laravel'ning standart pagination yorliqlari o'rniga sof strelka
// ikonkalarini ko'rsatish uchun (Foydalanuvchilar sahifasidagi bilan
// bir xil naqsh).
const isPrevLabel = (label) => /Previous|&laquo;|«/i.test(label)
const isNextLabel = (label) => /Next|&raquo;|»/i.test(label)
</script>
