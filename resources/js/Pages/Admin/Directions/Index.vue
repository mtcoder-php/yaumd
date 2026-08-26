<template>
    <AppLayout title="Yo'nalishlar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Yo'nalishlar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Barcha bakalavr va magistr yo'nalishlari</p>
                </div>
                <Link :href="route('admin.directions.create')" class="btn-primary">
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    Yangi yo'nalish
                </Link>
            </div>

            <!-- Fakultet tabs -->
            <div class="overflow-x-auto scrollbar-hide">
                <div class="flex items-center gap-2 w-max">
                    <button
                        @click="activeFaculty = null"
                        class="px-4 py-2 text-sm font-medium rounded-xl border transition-all whitespace-nowrap"
                        :class="activeFaculty === null
                            ? 'border-[#0f3460] text-[#0f3460] bg-blue-50'
                            : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                    >
                        Barchasi ({{ totalDirections }})
                    </button>
                    <button
                        v-for="f in faculties"
                        :key="f.id"
                        @click="activeFaculty = f.id"
                        class="px-4 py-2 text-sm font-medium rounded-xl border transition-all whitespace-nowrap"
                        :class="activeFaculty === f.id
                            ? 'border-[#0f3460] text-[#0f3460] bg-blue-50'
                            : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                    >
                        {{ f.short_name || f.name_uz }} ({{ f.directions?.length || 0 }})
                    </button>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Yo'nalish</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Fakultet</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Daraja</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Kvota</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Ariza</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                    <tr v-if="!filteredDirections.length">
                        <td colspan="7" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:school-off-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Yo'nalish topilmadi</p>
                        </td>
                    </tr>
                    <tr v-for="d in filteredDirections" :key="d.id"
                        class="hover:bg-gray-50 transition-colors">

                        <!-- Yo'nalish -->
                        <td class="px-4 py-3">
                            <p class="text-sm font-medium text-gray-900">{{ d.name_uz }}</p>
                            <p class="text-xs text-gray-400">{{ d.hemis_code || '—' }}</p>
                        </td>

                        <!-- Fakultet -->
                        <td class="px-4 py-3">
                            <p class="text-xs text-gray-600">{{ d.faculty?.short_name || d.faculty?.name_uz }}</p>
                        </td>

                        <!-- Daraja -->
                        <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                      :class="d.degree === 'bachelor'
                                        ? 'bg-blue-50 text-blue-700'
                                        : 'bg-purple-50 text-purple-700'">
                                    {{ d.degree === 'bachelor' ? 'Bakalavr' : 'Magistr' }}
                                    {{ d.duration_years }}y
                                </span>
                        </td>

                        <!-- Kvota -->
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-1 text-xs text-gray-600">
                                <span class="text-green-600 font-semibold">{{ d.quota_grant || 0 }}</span>
                                <span class="text-gray-300">/</span>
                                <span class="text-blue-600 font-semibold">{{ d.quota_contract || 0 }}</span>
                            </div>
                            <p class="text-xs text-gray-400">Grant / Kontrakt</p>
                        </td>

                        <!-- Arizalar -->
                        <td class="px-4 py-3">
                                <span class="text-sm font-semibold text-gray-700">
                                    {{ d.applicants_count || 0 }}
                                </span>
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
                                <Link :href="route('admin.directions.edit', d.id)"
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
            </div>
        </div>

        <!-- Delete modal -->
        <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="deleteTarget = null">
            <div class="bg-white rounded-2xl w-full max-w-sm p-6">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <Icon icon="mdi:delete-outline" class="w-6 h-6 text-red-500" />
                </div>
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Yo'nalishni o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ deleteTarget?.name_uz }}</strong> yo'nalishini o'chirasizmi?
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
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    faculties: { type: Array, default: () => [] },
})

const activeFaculty = ref(null)
const deleteTarget  = ref(null)

const totalDirections = computed(() =>
    props.faculties.reduce((sum, f) => sum + (f.directions?.length || 0), 0)
)

const filteredDirections = computed(() => {
    const all = props.faculties.flatMap(f =>
        (f.directions || []).map(d => ({ ...d, faculty: f }))
    )
    if (activeFaculty.value === null) return all
    return all.filter(d => d.faculty_id === activeFaculty.value)
})

const confirmDelete = (d) => { deleteTarget.value = d }

const submitDelete = () => {
    router.delete(route('admin.directions.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null },
    })
}
</script>

<style scoped>
.scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
.scrollbar-hide::-webkit-scrollbar { display: none; }

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
    transition: all 0.2s;
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
