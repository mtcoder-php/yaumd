<template>
    <AppLayout title="Fakultetlar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Fakultetlar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Jami: {{ faculties.total }} ta fakultet</p>
                </div>
                <Link :href="route('admin.faculties.create')" class="btn-primary">
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    Yangi fakultet
                </Link>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Fakultet</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Qisqa nomi</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Dekan</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Kafedralar</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Yo'nalishlar</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                    <tr v-if="!faculties.data?.length">
                        <td colspan="7" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:school-off-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Fakultet topilmadi</p>
                        </td>
                    </tr>
                    <tr v-for="f in faculties.data ?? []" :key="f.id"
                        class="hover:bg-gray-50 transition-colors">

                        <!-- Fakultet -->
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white text-xs flex-shrink-0"
                                     style="background: linear-gradient(135deg, #0f3460, #533483)">
                                    {{ f.short_name?.substring(0, 2)?.toUpperCase() || 'F' }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ f.name_uz }}</p>
                                    <p class="text-xs text-gray-400">{{ f.name_ru }}</p>
                                </div>
                            </div>
                        </td>

                        <!-- Qisqa nomi -->
                        <td class="px-4 py-3">
                            <span class="text-sm font-mono font-semibold text-gray-700">{{ f.short_name }}</span>
                        </td>

                        <!-- Dekan -->
                        <td class="px-4 py-3 text-sm text-gray-600">
                            {{ f.dean?.full_name || '—' }}
                        </td>

                        <!-- Kafedralar -->
                        <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-sm font-bold"
                                      style="background: linear-gradient(135deg, #eff6ff, #f5f3ff); color: #0f3460">
                                    {{ f.departments_count }}
                                </span>
                        </td>

                        <!-- Yo'nalishlar -->
                        <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-sm font-bold"
                                      style="background: linear-gradient(135deg, #f0fdf4, #dcfce7); color: #16a34a">
                                    {{ f.directions_count }}
                                </span>
                        </td>

                        <!-- Status -->
                        <td class="px-4 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                      :class="f.is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500'">
                                    {{ f.is_active ? 'Faol' : 'Nofaol' }}
                                </span>
                        </td>

                        <!-- Amallar -->
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <Link :href="route('admin.faculties.edit', f.id)"
                                      class="text-xs font-medium text-amber-600 hover:text-amber-800 flex items-center gap-1">
                                    <Icon icon="mdi:pencil-outline" class="w-3.5 h-3.5" />
                                    Tahrir
                                </Link>
                                <button @click="confirmDelete(f)"
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
                <div v-if="(faculties.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500">{{ faculties.from }}–{{ faculties.to }} / {{ faculties.total }}</p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in (faculties.links ?? [])" :key="link.label">
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
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Fakultetni o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ deleteTarget?.name_uz }}</strong> fakultetini o'chirasizmi?
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
    faculties: { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
})

const deleteTarget = ref(null)
const confirmDelete = (f) => { deleteTarget.value = f }
const submitDelete = () => {
    router.delete(route('admin.faculties.destroy', deleteTarget.value.id), {
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
