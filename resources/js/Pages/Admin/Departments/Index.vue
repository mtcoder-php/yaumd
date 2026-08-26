<template>
    <AppLayout title="Kafedralar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Kafedralar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Jami: {{ departments.total }} ta kafedra</p>
                </div>
                <Link :href="route('admin.departments.create')" class="btn-primary">
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    Yangi kafedra
                </Link>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Kafedra</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Fakultet</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Mudiri</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Yo'nalishlar</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                    <tr v-if="!departments.data?.length">
                        <td colspan="6" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:office-building-off-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Kafedra topilmadi</p>
                        </td>
                    </tr>
                    <tr v-for="d in departments.data ?? []" :key="d.id"
                        class="hover:bg-gray-50 transition-colors">

                        <!-- Kafedra -->
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-white text-xs flex-shrink-0"
                                     style="background: linear-gradient(135deg, #0f3460, #533483)">
                                    {{ d.short_name?.substring(0, 2)?.toUpperCase() || 'K' }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ d.name_uz }}</p>
                                    <p class="text-xs text-gray-400">{{ d.name_ru }}</p>
                                </div>
                            </div>
                        </td>

                        <!-- Fakultet -->
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ d.faculty?.short_name || d.faculty?.name_uz }}
                        </td>

                        <!-- Mudiri -->
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ d.head?.full_name || '—' }}
                        </td>

                        <!-- Yo'nalishlar -->
                        <td class="px-4 py-3">
                            <span class="text-sm font-semibold text-gray-700">{{ d.directions_count }}</span>
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                      :class="d.is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500'">
                                    {{ d.is_active ? 'Faol' : 'Nofaol' }}
                                </span>
                        </td>

                        <!-- Amallar -->
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <Link :href="route('admin.departments.edit', d.id)"
                                      class="text-xs font-medium text-amber-600 hover:text-amber-800 flex items-center gap-1">
                                    <Icon icon="mdi:pencil-outline" class="w-3.5 h-3.5" />
                                    Tahrir
                                </Link>
                                <button @click="confirmDelete(d)"
                                        class="text-xs font-medium text-red-500 hover:text-red-700 flex items-center gap-1">
                                    <Icon icon="mdi:delete-outline" class="w-3.5 h-3.5" />
                                    O'chirish
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="(departments.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500">{{ departments.from }}–{{ departments.to }} / {{ departments.total }}</p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in (departments.links ?? [])" :key="link.label">
                            <Link v-if="link.url" :href="link.url"
                                  class="px-3 py-1.5 text-xs rounded-lg transition"
                                  :class="link.active ? 'text-white font-semibold' : 'text-gray-500 hover:bg-gray-100'"
                                  :style="link.active ? 'background:linear-gradient(135deg,#0f3460,#533483)' : ''"
                                  v-html="link.label" />
                            <span v-else class="px-3 py-1.5 text-xs text-gray-300" v-html="link.label" />
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
                    <button @click="deleteTarget = null" class="btn-secondary flex-1">Bekor qilish</button>
                    <button @click="submitDelete" class="btn-danger flex-1">O'chirish</button>
                </div>
            </div>
        </div>

    </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
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
</script>

<style scoped>
.btn-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.625rem 1.25rem;
    border-radius: 0.75rem;
    background: linear-gradient(135deg, #0f3460, #533483);
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.2s;
}
.btn-primary:hover { box-shadow: 0 6px 20px rgba(15,52,96,0.3); }
.btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.625rem 1.25rem;
    border-radius: 0.75rem;
    background: white;
    color: #374151;
    font-size: 0.875rem;
    font-weight: 600;
    border: 1.5px solid #e5e7eb;
    cursor: pointer;
}
.btn-secondary:hover { background: #f9fafb; }
.btn-danger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.625rem 1.25rem;
    border-radius: 0.75rem;
    background: #ef4444;
    color: white;
    font-size: 0.875rem;
    font-weight: 600;
    border: none;
    cursor: pointer;
}
.btn-danger:hover { background: #dc2626; }
</style>
