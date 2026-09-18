<template>
    <AppLayout title="Guruhlar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Guruhlar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Jami: {{ groups.total }} ta guruh</p>
                </div>
                <Link :href="route('admin.student-groups.create')" class="btn-brand">
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
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-brand-600 bg-gray-50"
                        @input="debouncedSearch"
                    >
                </div>

                <select v-model="filters.academic_year_id" @change="applyFilters" class="select-filter">
                    <option value="">Barcha o'quv yillari</option>
                    <option v-for="y in academicYears" :key="y.id" :value="y.id">{{ y.name }}</option>
                </select>

                <select v-model="filters.direction_id" @change="applyFilters" class="select-filter">
                    <option value="">Barcha yo'nalishlar</option>
                    <option v-for="d in directions" :key="d.id" :value="d.id">{{ d.name_uz }}</option>
                </select>

                <select v-model="filters.degree" @change="applyFilters" class="select-filter">
                    <option value="">Barcha darajalar</option>
                    <option value="bachelor">Bakalavr</option>
                    <option value="master">Magistr</option>
                </select>

                <select v-model="filters.course_year" @change="applyFilters" class="select-filter">
                    <option value="">Barcha kurslar</option>
                    <option v-for="c in 6" :key="c" :value="c">{{ c }}-kurs</option>
                </select>

                <button v-if="hasFilters" @click="resetFilters" class="btn-neutral">
                    <Icon icon="mdi:close" class="w-4 h-4" />
                    Tozalash
                </button>
            </div>

            <!-- Table -->
            <div class="table-grid-wrap">
                <table class="table-grid">
                    <thead>
                    <tr>
                        <th>Guruh</th>
                        <th>Yo'nalish</th>
                        <th>O'quv yili</th>
                        <th>Daraja / Shakl</th>
                        <th class="text-center">Kurs</th>
                        <th class="text-center">Talabalar</th>
                        <th>Status</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="!groups.data?.length">
                        <td colspan="8" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:account-group-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Guruh topilmadi</p>
                        </td>
                    </tr>
                    <tr v-for="g in groups.data ?? []" :key="g.id"
                        class="cursor-pointer"
                        @click="router.visit(route('admin.student-groups.show', g.id))">

                        <!-- Guruh -->
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-white text-xs flex-shrink-0 bg-brand-600">
                                    <Icon icon="mdi:bookmark-multiple-outline" class="w-5 h-5" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ g.name }}</p>
                                    <p v-if="g.hemis_id" class="text-xs text-gray-400">HEMIS: {{ g.hemis_id }}</p>
                                </div>
                            </div>
                        </td>

                        <!-- Yo'nalish -->
                        <td class="text-sm text-gray-600">{{ g.direction?.name_uz || '—' }}</td>

                        <!-- O'quv yili -->
                        <td class="text-sm text-gray-600">{{ g.academic_year?.name || '—' }}</td>

                        <!-- Daraja / Shakl -->
                        <td class="text-sm text-gray-600">
                            {{ degreeLabel(g.degree) }} · {{ studyFormLabel(g.study_form) }}
                        </td>

                        <!-- Kurs -->
                        <td class="text-center">
                            <span class="badge-pill badge-brand">{{ g.course_year }}-kurs</span>
                        </td>

                        <!-- Talabalar -->
                        <td class="text-center">
                            <span class="badge-pill badge-neutral">{{ g.students_count }}</span>
                        </td>

                        <!-- Status -->
                        <td>
                            <span class="badge-pill" :class="g.is_active ? 'badge-success' : 'badge-neutral'">
                                {{ g.is_active ? 'Faol' : 'Nofaol' }}
                            </span>
                        </td>

                        <!-- Amallar -->
                        <td @click.stop>
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="route('admin.student-groups.edit', g.id)" title="Tahrirlash" class="btn-ghost-icon">
                                    <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                                </Link>
                                <button @click="confirmDelete(g)" title="O'chirish" class="btn-ghost-icon danger">
                                    <Icon icon="mdi:delete-outline" class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="(groups.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-3">
                    <p class="text-xs text-gray-500">{{ groups.from }}–{{ groups.to }} / {{ groups.total }}</p>
                    <div class="flex items-center gap-1.5">
                        <template v-for="link in (groups.links ?? [])" :key="link.label">
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
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Guruhni o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ deleteTarget?.name }}</strong> guruhini o'chirasizmi?
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
import { ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'
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

// Laravel'ning standart pagination yorliqlari o'rniga sof strelka
// ikonkalarini ko'rsatish uchun (Foydalanuvchilar sahifasidagi bilan
// bir xil naqsh).
const isPrevLabel = (label) => /Previous|&laquo;|«/i.test(label)
const isNextLabel = (label) => /Next|&raquo;|»/i.test(label)
</script>
