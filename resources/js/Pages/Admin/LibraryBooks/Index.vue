<template>
    <AppLayout title="Kutubxona — Kitoblar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Kutubxona — Kitoblar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Jami: {{ books.total }} ta kitob</p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('admin.library-categories.index')" class="btn-neutral">
                        <Icon icon="mdi:bookshelf" class="w-4 h-4" />
                        Kategoriyalar
                    </Link>
                    <Link :href="route('admin.library.create')" class="btn-brand">
                        <Icon icon="mdi:plus" class="w-4 h-4" />
                        Yangi kitob
                    </Link>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl border border-gray-100 p-4 flex flex-wrap gap-3"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <div class="flex-1 min-w-48 relative">
                    <Icon icon="mdi:magnify" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Nomi, muallif yoki ISBN..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-brand-600 bg-gray-50"
                        @input="debouncedSearch"
                    >
                </div>

                <select v-model="filters.category_id" @change="applyFilters" class="select-filter">
                    <option value="">Barcha kategoriyalar</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name_uz }}</option>
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
                        <th>Kitob</th>
                        <th>Kategoriya</th>
                        <th>Joylashuvi</th>
                        <th class="text-center">Nusxalar</th>
                        <th>Elektron</th>
                        <th>Status</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="!books.data?.length">
                        <td colspan="7" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:book-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Kitob topilmadi</p>
                        </td>
                    </tr>
                    <tr v-for="b in books.data ?? []" :key="b.id"
                        class="cursor-pointer"
                        @click="router.visit(route('admin.library.show', b.id))">

                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-12 rounded-lg bg-gray-100 overflow-hidden flex items-center justify-center flex-shrink-0">
                                    <img v-if="b.cover_image_url" :src="b.cover_image_url" class="w-full h-full object-cover" alt="">
                                    <Icon v-else icon="mdi:book-outline" class="w-5 h-5 text-gray-400" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ b.title }}</p>
                                    <p class="text-xs text-gray-400">{{ b.author }}</p>
                                </div>
                            </div>
                        </td>

                        <td class="text-sm text-gray-600">{{ b.category?.name_uz || '—' }}</td>
                        <td class="text-sm text-gray-600">{{ b.shelf_location || '—' }}</td>
                        <td class="text-center">
                            <span class="inline-flex items-center justify-center px-2.5 h-8 rounded-xl text-sm font-bold whitespace-nowrap"
                                  :class="b.available_copies_count > 0 ? 'bg-brand-50 text-brand-600' : 'bg-red-50 text-red-600'">
                                {{ b.available_copies_count }} / {{ b.copies_count }}
                            </span>
                        </td>
                        <td>
                            <span v-if="!b.has_digital_file" class="text-xs text-gray-400">—</span>
                            <span v-else-if="b.access_type === 'paid'" class="badge-pill badge-warning">
                                {{ formatPrice(b.price) }}
                            </span>
                            <span v-else class="badge-pill badge-brand">Bepul</span>
                        </td>
                        <td>
                            <span class="badge-pill" :class="b.is_active ? 'badge-success' : 'badge-neutral'">
                                {{ b.is_active ? 'Faol' : 'Nofaol' }}
                            </span>
                        </td>
                        <td @click.stop>
                            <div class="flex items-center justify-end gap-1">
                                <Link :href="route('admin.library.edit', b.id)" title="Tahrirlash" class="btn-ghost-icon">
                                    <Icon icon="mdi:pencil-outline" class="w-4 h-4" />
                                </Link>
                                <button @click="confirmDelete(b)" title="O'chirish" class="btn-ghost-icon danger">
                                    <Icon icon="mdi:delete-outline" class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="(books.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-3">
                    <p class="text-xs text-gray-500">{{ books.from }}–{{ books.to }} / {{ books.total }}</p>
                    <div class="flex items-center gap-1.5">
                        <template v-for="link in (books.links ?? [])" :key="link.label">
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
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Kitobni o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ deleteTarget?.title }}</strong> kitobini o'chirasizmi?
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
    books:      { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
    categories: { type: Array, default: () => [] },
    filters:    { type: Object, default: () => ({}) },
})

const filters = ref({
    search:      props.filters.search || '',
    category_id: props.filters.category_id || '',
})

const hasFilters = computed(() => Object.values(filters.value).some(v => v))

const formatPrice = (v) => new Intl.NumberFormat('uz-UZ').format(v) + " so'm"

const applyFilters = () => {
    router.get(route('admin.library.index'), filters.value, {
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
    filters.value = { search: '', category_id: '' }
    applyFilters()
}

const deleteTarget = ref(null)
const confirmDelete = (b) => { deleteTarget.value = b }
const submitDelete = () => {
    router.delete(route('admin.library.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null },
    })
}

// Laravel'ning standart pagination yorliqlari o'rniga sof strelka
// ikonkalarini ko'rsatish uchun (boshqa sahifalardagi bilan bir xil naqsh).
const isPrevLabel = (label) => /Previous|&laquo;|«/i.test(label)
const isNextLabel = (label) => /Next|&raquo;|»/i.test(label)
</script>
