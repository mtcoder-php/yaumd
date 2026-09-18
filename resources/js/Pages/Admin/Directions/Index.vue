<template>
    <AppLayout title="Yo'nalishlar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Yo'nalishlar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Barcha bakalavr va magistr yo'nalishlari</p>
                </div>
                <Link :href="route('admin.directions.create')" class="btn-brand">
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    Yangi yo'nalish
                </Link>
            </div>

            <!-- Kafedra tabs -->
            <div class="overflow-x-auto scrollbar-hide">
                <div class="flex items-center gap-2 w-max">
                    <button
                        @click="activeDepartment = null"
                        class="px-4 py-2 text-sm font-medium rounded-xl border transition-all whitespace-nowrap"
                        :class="activeDepartment === null
                            ? 'border-brand-600 text-brand-600 bg-brand-50'
                            : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                    >
                        Barchasi ({{ totalDirections }})
                    </button>
                    <button
                        v-for="dep in departments"
                        :key="dep.id"
                        @click="activeDepartment = dep.id"
                        class="px-4 py-2 text-sm font-medium rounded-xl border transition-all whitespace-nowrap"
                        :class="activeDepartment === dep.id
                            ? 'border-brand-600 text-brand-600 bg-brand-50'
                            : 'border-gray-200 text-gray-500 hover:border-gray-300'"
                    >
                        {{ dep.short_name || dep.name_uz }} ({{ dep.directions?.length || 0 }})
                    </button>
                </div>
            </div>

            <!-- Table — to'liq to'r (grid) chegarali dizayn (Foydalanuvchilar
                 sahifasida o'rnatilgan .table-grid* naqshi). Serverda pagination
                 yo'q — kafedra tablari orqali mijoz tomonda filtrlash saqlanadi. -->
            <div class="table-grid-wrap">
                <table class="table-grid">
                    <thead>
                    <tr>
                        <th>Yo'nalish</th>
                        <th>Fakultet</th>
                        <th>Daraja</th>
                        <th class="text-center">Kvota</th>
                        <th class="text-center">Ariza</th>
                        <th>Status</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="!filteredDirections.length">
                        <td colspan="7" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:school-off-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Yo'nalish topilmadi</p>
                        </td>
                    </tr>
                    <tr v-for="d in filteredDirections" :key="d.id">

                        <!-- Yo'nalish -->
                        <td>
                            <Link :href="route('admin.directions.show', d.id)" class="group">
                                <p class="text-sm font-semibold text-gray-900 group-hover:text-brand-600 transition">{{ d.name_uz }}</p>
                                <p class="text-xs text-gray-400">{{ d.hemis_code || '—' }}</p>
                            </Link>
                        </td>

                        <!-- Fakultet -->
                        <td class="text-sm text-gray-600">
                            {{ d.faculty?.short_name || d.faculty?.name_uz || '—' }}
                        </td>

                        <!-- Daraja -->
                        <td>
                            <span class="badge-pill" :class="d.degree === 'bachelor' ? 'badge-brand' : 'badge-warning'">
                                {{ d.degree === 'bachelor' ? 'Bakalavr' : 'Magistr' }} · {{ d.duration_years }}y
                            </span>
                        </td>

                        <!-- Kvota -->
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1 text-xs">
                                <span class="text-green-600 font-semibold">{{ d.quota_grant || 0 }}</span>
                                <span class="text-gray-300">/</span>
                                <span class="text-blue-600 font-semibold">{{ d.quota_contract || 0 }}</span>
                            </div>
                            <p class="text-[10px] text-gray-400">Grant / Kontrakt</p>
                        </td>

                        <!-- Arizalar -->
                        <td class="text-center">
                            <span class="badge-pill badge-neutral">{{ d.applicants_count || 0 }}</span>
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
                                <Link :href="route('admin.directions.show', d.id)" title="Ko'rish" class="btn-ghost-icon">
                                    <Icon icon="mdi:eye-outline" class="w-4 h-4" />
                                </Link>
                                <Link :href="route('admin.directions.edit', d.id)" title="Tahrirlash" class="btn-ghost-icon">
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
                    <button @click="deleteTarget = null" class="btn-neutral flex-1 justify-center">Bekor qilish</button>
                    <button @click="submitDelete" class="btn-danger-pill flex-1">O'chirish</button>
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
    departments: { type: Array, default: () => [] },
})

const activeDepartment = ref(null)
const deleteTarget      = ref(null)

const totalDirections = computed(() =>
    props.departments.reduce((sum, dep) => sum + (dep.directions?.length || 0), 0)
)

const filteredDirections = computed(() => {
    const all = props.departments.flatMap(dep =>
        (dep.directions || []).map(d => ({ ...d, department: dep }))
    )
    if (activeDepartment.value === null) return all
    return all.filter(d => d.department_id === activeDepartment.value)
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
</style>
