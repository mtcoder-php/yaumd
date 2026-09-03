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
                    <Link :href="route('admin.library-categories.index')" class="btn-secondary">
                        <Icon icon="mdi:bookshelf" class="w-4 h-4" />
                        Kategoriyalar
                    </Link>
                    <Link :href="route('admin.library.create')" class="btn-primary">
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
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50"
                        @input="debouncedSearch"
                    >
                </div>

                <select v-model="filters.category_id" @change="applyFilters"
                        class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50">
                    <option value="">Barcha kategoriyalar</option>
                    <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name_uz }}</option>
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
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Kitob</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Kategoriya</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Joylashuvi</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Nusxalar</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                        <tr v-if="!books.data?.length">
                            <td colspan="6" class="text-center py-16 text-gray-400">
                                <Icon icon="mdi:book-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                                <p class="text-sm">Kitob topilmadi</p>
                            </td>
                        </tr>
                        <tr v-for="b in books.data ?? []" :key="b.id"
                            class="hover:bg-gray-50 transition-colors cursor-pointer"
                            @click="router.visit(route('admin.library.show', b.id))">

                            <td class="px-4 py-3">
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

                            <td class="px-4 py-3 text-sm text-gray-600">{{ b.category?.name_uz || '—' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ b.shelf_location || '—' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center px-2.5 h-8 rounded-xl text-sm font-bold whitespace-nowrap"
                                      :class="b.available_copies_count > 0 ? '' : 'bg-red-50 text-red-600'"
                                      :style="b.available_copies_count > 0 ? 'background: linear-gradient(135deg, #eff6ff, #f5f3ff); color: #0f3460' : ''">
                                    {{ b.available_copies_count }} / {{ b.copies_count }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                      :class="b.is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500'">
                                    {{ b.is_active ? 'Faol' : 'Nofaol' }}
                                </span>
                            </td>
                            <td class="px-4 py-3" @click.stop>
                                <div class="flex items-center gap-3">
                                    <Link :href="route('admin.library.edit', b.id)"
                                          class="text-xs font-medium text-amber-600 hover:text-amber-800 flex items-center gap-1">
                                        <Icon icon="mdi:pencil-outline" class="w-3.5 h-3.5" />
                                        Tahrir
                                    </Link>
                                    <button @click="confirmDelete(b)"
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
                <div v-if="(books.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500">{{ books.from }}–{{ books.to }} / {{ books.total }}</p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in (books.links ?? [])" :key="link.label">
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
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Kitobni o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ deleteTarget?.title }}</strong> kitobini o'chirasizmi?
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
    books:      { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
    categories: { type: Array, default: () => [] },
    filters:    { type: Object, default: () => ({}) },
})

const filters = ref({
    search:      props.filters.search || '',
    category_id: props.filters.category_id || '',
})

const hasFilters = computed(() => Object.values(filters.value).some(v => v))

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
    gap: 0.5rem;
    justify-content: center;
    padding: 0.625rem 1.25rem;
    border-radius: 0.75rem;
    background: white;
    color: #374151;
    font-size: 0.875rem;
    font-weight: 600;
    border: 1.5px solid #e5e7eb;
    cursor: pointer;
    text-decoration: none;
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
