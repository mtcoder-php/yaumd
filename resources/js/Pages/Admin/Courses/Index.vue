<template>
    <AppLayout title="Kurslar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Kurslar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Jami: {{ courses.total }} ta kurs</p>
                </div>
                <Link :href="route('admin.courses.create')" class="btn-brand">
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    Yangi kurs
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
                        placeholder="Kurs nomi..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-brand-600 bg-gray-50"
                        @input="debouncedSearch"
                    >
                </div>

                <select v-model="filters.category_id" @change="applyFilters" class="select-filter">
                    <option value="">Barcha kategoriyalar</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name_uz }}</option>
                </select>

                <select v-model="filters.type" @change="applyFilters" class="select-filter">
                    <option value="">Barcha turlar</option>
                    <option value="open">Ochiq</option>
                    <option value="free">Bepul</option>
                    <option value="paid">Pullik</option>
                    <option value="students_only">Faqat talabalar</option>
                </select>

                <select v-model="filters.status" @change="applyFilters" class="select-filter">
                    <option value="">Barcha statuslar</option>
                    <option value="draft">Qoralama</option>
                    <option value="published">Nashr qilingan</option>
                    <option value="archived">Arxivlangan</option>
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
                        <th>Kurs</th>
                        <th>Kategoriya</th>
                        <th>Turi</th>
                        <th>Narxi</th>
                        <th class="text-center">Talabalar</th>
                        <th>Status</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="!courses.data?.length">
                        <td colspan="7" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:school-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Kurs topilmadi</p>
                        </td>
                    </tr>
                    <tr v-for="c in courses.data ?? []" :key="c.id"
                        class="cursor-pointer"
                        @click="router.visit(route('admin.courses.show', c.id))">

                        <!-- Kurs -->
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-gray-100 overflow-hidden flex items-center justify-center flex-shrink-0">
                                    <img v-if="c.thumbnail_url" :src="c.thumbnail_url" class="w-full h-full object-cover" alt="">
                                    <Icon v-else icon="mdi:school-outline" class="w-5 h-5 text-gray-400" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ c.title_uz }}</p>
                                    <p class="text-xs text-gray-400">{{ c.creator?.full_name || '—' }}</p>
                                </div>
                            </div>
                        </td>

                        <td class="text-sm text-gray-600">{{ c.category?.name_uz || '—' }}</td>
                        <td class="text-sm text-gray-600">{{ typeLabel(c.type) }}</td>
                        <td class="text-sm text-gray-600">
                            <span v-if="c.discount_price" class="line-through text-gray-400 mr-1">{{ formatPrice(c.price) }}</span>
                            {{ Number(c.discount_price) > 0 ? formatPrice(c.discount_price) : formatPrice(c.price) }}
                        </td>
                        <td class="text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-sm font-bold bg-brand-50 text-brand-600">
                                {{ c.enrollments_count }}
                            </span>
                        </td>
                        <td>
                            <span class="badge-pill" :class="statusClass(c.status)">
                                {{ statusLabel(c.status) }}
                            </span>
                        </td>
                        <td @click.stop>
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="route('admin.courses.edit', c.id)" title="Tahrirlash" class="btn-ghost-icon">
                                    <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                                </Link>
                                <button @click="confirmDelete(c)" title="O'chirish" class="btn-ghost-icon danger">
                                    <Icon icon="mdi:delete-outline" class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="(courses.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-3">
                    <p class="text-xs text-gray-500">{{ courses.from }}–{{ courses.to }} / {{ courses.total }}</p>
                    <div class="flex items-center gap-1.5">
                        <template v-for="link in (courses.links ?? [])" :key="link.label">
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
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Kursni o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ deleteTarget?.title_uz }}</strong> kursini o'chirasizmi?
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
    courses:    { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
    categories: { type: Array, default: () => [] },
    filters:    { type: Object, default: () => ({}) },
})

const filters = ref({
    search:      props.filters.search || '',
    category_id: props.filters.category_id || '',
    type:        props.filters.type || '',
    status:      props.filters.status || '',
})

const typeLabel = (v) => ({ open: 'Ochiq', free: 'Bepul', paid: 'Pullik', students_only: 'Faqat talabalar' }[v] || v)
const statusLabel = (v) => ({ draft: 'Qoralama', published: 'Nashr qilingan', archived: 'Arxivlangan' }[v] || v)
const statusClass = (v) => ({
    draft:     'badge-neutral',
    published: 'badge-success',
    archived:  'badge-warning',
}[v] || 'badge-neutral')

const formatPrice = (v) => Number(v) > 0 ? new Intl.NumberFormat('uz-UZ').format(v) + " so'm" : 'Bepul'

const hasFilters = computed(() => Object.values(filters.value).some(v => v))

const applyFilters = () => {
    router.get(route('admin.courses.index'), filters.value, {
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
    filters.value = { search: '', category_id: '', type: '', status: '' }
    applyFilters()
}

const deleteTarget = ref(null)
const confirmDelete = (c) => { deleteTarget.value = c }
const submitDelete = () => {
    router.delete(route('admin.courses.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null },
    })
}

// Laravel'ning standart pagination yorliqlari o'rniga sof strelka
// ikonkalarini ko'rsatish uchun (boshqa sahifalardagi bilan bir xil naqsh).
const isPrevLabel = (label) => /Previous|&laquo;|«/i.test(label)
const isNextLabel = (label) => /Next|&raquo;|»/i.test(label)
</script>
