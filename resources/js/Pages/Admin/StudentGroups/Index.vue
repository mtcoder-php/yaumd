<template>
    <AppLayout title="Guruhlar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Guruhlar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Jami: {{ groups.total }} ta guruh</p>
                </div>
                <Link :href="route('admin.student-groups.create')" class="btn-primary">
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    Yangi guruh
                </Link>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl border border-gray-100 p-4 flex flex-wrap gap-3"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <div class="flex-1 min-w-48 relative">
                    <Icon icon="mdi:magnify" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Guruh nomi, HEMIS ID..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50"
                        @input="debouncedSearch"
                    >
                </div>

                <select v-model="filters.academic_year_id" @change="applyFilters"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50">
                    <option value="">Barcha o'quv yillari</option>
                    <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.name }}</option>
                </select>

                <select v-model="filters.direction_id" @change="applyFilters"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50">
                    <option value="">Barcha yo'nalishlar</option>
                    <option v-for="d in directions" :key="d.id" :value="d.id">{{ d.name_uz }}</option>
                </select>

                <select v-model="filters.degree" @change="applyFilters"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50">
                    <option value="">Barcha darajalar</option>
                    <option value="bachelor">Bakalavr</option>
                    <option value="master">Magistr</option>
                </select>

                <select v-model="filters.course_year" @change="applyFilters"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50">
                    <option value="">Barcha kurslar</option>
                    <option v-for="c in 6" :key="c" :value="c">{{ c }}-kurs</option>
                </select>

                <button v-if="hasFilters" @click="resetFilters"
                        class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 flex items-center gap-1.5">
                    <Icon icon="mdi:close" class="w-4 h-4" />
                    Tozalash
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Guruh</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Yo'nalish</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">O'quv yili</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Daraja / Shakl</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Kurs</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Talabalar</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                        <tr v-if="!groups.data?.length">
                            <td colspan="8" class="text-center py-16 text-gray-400">
                                <Icon icon="mdi:account-group-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                                <p class="text-sm">Guruh topilmadi</p>
                            </td>
                        </tr>
                        <tr v-for="g in groups.data ?? []" :key="g.id"
                            class="hover:bg-gray-50 transition-colors cursor-pointer"
                            @click="router.visit(route('admin.student-groups.show', g.id))">

                            <!-- Guruh -->
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center font-bold text-white text-xs flex-shrink-0"
                                         style="background: linear-gradient(135deg, #0f3460, #533483)">
                                        <Icon icon="mdi:bookmark-multiple-outline" class="w-5 h-5" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900">{{ g.name }}</p>
                                        <p v-if="g.hemis_id" class="text-xs text-gray-400">HEMIS: {{ g.hemis_id }}</p>
                                    </div>
                                </div>
                            </td>

                            <!-- Yo'nalish -->
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ g.direction?.name_uz || '—' }}
                            </td>

                            <!-- O'quv yili -->
                            <td class="px-4 py-3 text-sm text-gray-600">{{ g.academic_year?.name || '—' }}</td>

                            <!-- Daraja / Shakl -->
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ degreeLabel(g.degree) }} · {{ studyFormLabel(g.study_form) }}
                            </td>

                            <!-- Kurs -->
                            <td class="px-4 py-3 text-center text-sm text-gray-600">{{ g.course_year }}-kurs</td>

                            <!-- Talabalar -->
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-sm font-bold"
                                      style="background: linear-gradient(135deg, #eff6ff, #f5f3ff); color: #0f3460">
                                    {{ g.students_count }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                      :class="g.is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500'">
                                    {{ g.is_active ? 'Faol' : 'Nofaol' }}
                                </span>
                            </td>

                            <!-- Amallar -->
                            <td class="px-4 py-3" @click.stop>
                                <div class="flex items-center gap-3">
                                    <Link :href="route('admin.student-groups.edit', g.id)"
                                          class="text-xs font-medium text-amber-600 hover:text-amber-800 flex items-center gap-1">
                                        <Icon icon="mdi:pencil-outline" class="w-3.5 h-3.5" />
                                        Tahrir
                                    </Link>
                                    <button @click="confirmDelete(g)"
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

                <!-- Pagination -->
                <div v-if="(groups.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500">{{ groups.from }}–{{ groups.to }} / {{ groups.total }}</p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in (groups.links ?? [])" :key="link.label">
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
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Guruhni o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ deleteTarget?.name }}</strong> guruhini o'chirasizmi?
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
    groups:        { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
    academicYears: { type: Array, default: () => [] },
    directions:    { type: Array, default: () => [] },
    filters:       { type: Object, default: () => ({}) },
})

const filters = ref({
    search:            props.filters.search || '',
    academic_year_id:  props.filters.academic_year_id || '',
    direction_id:      props.filters.direction_id || '',
    degree:            props.filters.degree || '',
    course_year:       props.filters.course_year || '',
})

const degreeLabel = (v) => ({ bachelor: 'Bakalavr', master: 'Magistr' }[v] || v)
const studyFormLabel = (v) => ({ full_time: 'Kunduzgi', evening: 'Kechki', distance: 'Sirtqi' }[v] || v)

const hasFilters = computed(() => Object.values(filters.value).some(v => v))

const applyFilters = () => {
    router.get(route('admin.student-groups.index'), filters.value, {
        preserveState: true,
        replace: true,
    })
}

let searchTimer = null
const debouncedSearch = () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(applyFilters, 400)
}

const resetFilters = () => {
    filters.value = { search: '', academic_year_id: '', direction_id: '', degree: '', course_year: '' }
    applyFilters()
}

const deleteTarget = ref(null)
const confirmDelete = (g) => { deleteTarget.value = g }
const submitDelete = () => {
    router.delete(route('admin.student-groups.destroy', deleteTarget.value.id), {
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
