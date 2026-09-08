<template>
    <AppLayout title="Kurslar katalogi">
        <div class="max-w-5xl mx-auto space-y-5">

            <div>
                <h1 class="text-xl font-bold text-gray-900">Kurslar katalogi</h1>
                <p class="text-sm text-gray-500 mt-0.5">Barcha ochiq kurslar — jami {{ courses.total }} ta kurs</p>
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

            <!-- Kurslar -->
            <div v-if="!courses.data?.length"
                 class="bg-white rounded-2xl border border-gray-100 p-16 text-center text-gray-400"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <Icon icon="mdi:book-search-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                <p class="text-sm">Kurs topilmadi</p>
            </div>

            <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <Link v-for="c in courses.data" :key="c.id"
                      :href="route('admin.course-catalog.show', c.id)"
                      class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-md transition-all block"
                      style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="aspect-video bg-gray-100 overflow-hidden flex items-center justify-center">
                        <img v-if="c.thumbnail_url" :src="c.thumbnail_url" class="w-full h-full object-cover" alt="">
                        <Icon v-else icon="mdi:book-open-page-variant-outline" class="w-10 h-10 text-gray-300" />
                    </div>
                    <div class="p-4">
                        <p class="text-sm font-semibold text-gray-900 line-clamp-2">{{ c.title_uz }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">{{ c.category?.name_uz || 'Kategoriyasiz' }}</p>
                        <div class="flex flex-wrap items-center gap-1 mt-2.5">
                            <span v-if="c.is_enrolled" class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-green-50 text-green-700">
                                Yozilgansiz
                            </span>
                            <span v-else-if="c.type === 'paid'" class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">
                                {{ formatPrice(c.discount_price > 0 ? c.discount_price : c.price) }}
                            </span>
                            <span v-else class="inline-flex px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                                Bepul
                            </span>
                            <span v-if="c.duration_hours" class="text-xs text-gray-400">· {{ c.duration_hours }} soat</span>
                        </div>
                    </div>
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="(courses.last_page ?? 1) > 1" class="flex items-center justify-center gap-1">
                <template v-for="link in (courses.links ?? [])" :key="link.label">
                    <Link v-if="link.url" :href="link.url"
                          class="px-3 py-1.5 text-xs rounded-lg transition"
                          :class="link.active ? 'text-white font-semibold' : 'text-gray-500 hover:bg-gray-100'"
                          :style="link.active ? 'background:linear-gradient(135deg,#0f3460,#533483)' : ''"
                          v-html="link.label" />
                    <span v-else class="px-3 py-1.5 text-xs text-gray-300" v-html="link.label" />
                </template>
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
    courses:    { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
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
    router.get(route('admin.course-catalog.index'), filters.value, {
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
</script>
