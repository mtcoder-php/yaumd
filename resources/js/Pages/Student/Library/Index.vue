<template>
    <AppLayout title="Kutubxona">
        <div class="max-w-5xl mx-auto space-y-5">

            <div>
                <h1 class="text-xl font-bold text-gray-900">Kutubxona</h1>
                <p class="text-sm text-gray-500 mt-0.5">Universitet elektron kutubxona katalogi — jami {{ books.total }} ta kitob</p>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl border border-gray-100 p-4 flex flex-wrap gap-3"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="flex-1 min-w-48 relative">
                    <Icon icon="mdi:magnify" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Kitob nomi yoki muallif..."
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

            <!-- Kitoblar -->
            <div v-if="!books.data?.length"
                 class="bg-white rounded-2xl border border-gray-100 p-16 text-center text-gray-400"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <Icon icon="mdi:book-search-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                <p class="text-sm">Kitob topilmadi</p>
            </div>

            <div v-else class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                <Link v-for="b in books.data" :key="b.id"
                      :href="route('admin.my-library.show', b.id)"
                      class="bg-white rounded-2xl border border-gray-100 overflow-hidden hover:shadow-md transition-all block"
                      style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <div class="aspect-[3/4] bg-gray-100 overflow-hidden flex items-center justify-center">
                        <img v-if="b.cover_image_url" :src="b.cover_image_url" class="w-full h-full object-cover" alt="">
                        <Icon v-else icon="mdi:book-outline" class="w-10 h-10 text-gray-300" />
                    </div>
                    <div class="p-3">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ b.title }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ b.author }}</p>
                        <span class="inline-flex mt-2 px-2 py-0.5 rounded-full text-xs font-semibold"
                              :class="b.available_copies_count > 0 ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600'">
                            {{ b.available_copies_count > 0 ? `${b.available_copies_count} nusxa mavjud` : 'Barchasi band' }}
                        </span>
                    </div>
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="(books.last_page ?? 1) > 1" class="flex items-center justify-center gap-1">
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
    router.get(route('admin.my-library.index'), filters.value, {
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
