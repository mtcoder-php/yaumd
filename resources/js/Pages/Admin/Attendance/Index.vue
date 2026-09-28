<template>
    <AppLayout title="Xodimlar davomati">
        <div class="space-y-5">

            <!-- Header -->
            <div>
                <h1 class="text-xl font-bold text-gray-900">Xodimlar davomati (kirish/chiqish)</h1>
                <p class="text-sm text-gray-500 mt-0.5">
                    Turniketdan o'tgan xodimlarning kirish/chiqish tarixi — sana, xodim yoki
                    yo'nalish bo'yicha filtrlashingiz mumkin.
                </p>
            </div>

            <!-- Statistika -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="bg-white rounded-2xl border border-gray-100 p-4" style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <p class="text-xs text-gray-500">Jami voqea</p>
                    <p class="text-xl font-bold text-gray-900">{{ stats.total_events }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-gray-100 p-4" style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                    <p class="text-xs text-gray-500">Nechta xodim</p>
                    <p class="text-xl font-bold text-gray-900">{{ stats.staff_count }}</p>
                </div>
            </div>

            <!-- Filtrlar -->
            <div class="bg-white rounded-2xl border border-gray-100 p-4 flex flex-wrap gap-3 items-end"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Sana</label>
                    <input v-model="filters.date" type="date"
                           class="text-sm border border-gray-200 rounded-xl px-3 py-2 bg-gray-50 focus:outline-none focus:border-brand-600"
                           @change="applyFilters">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Yo'nalish</label>
                    <select v-model="filters.direction"
                            class="text-sm border border-gray-200 rounded-xl px-3 py-2 bg-gray-50 focus:outline-none focus:border-brand-600"
                            @change="applyFilters">
                        <option value="">Barchasi</option>
                        <option value="kirish">Kirish</option>
                        <option value="chiqish">Chiqish</option>
                    </select>
                </div>
                <div class="flex-1 min-w-48 relative">
                    <label class="block text-xs text-gray-500 mb-1">Xodim (ism bo'yicha)</label>
                    <Icon icon="mdi:magnify" class="absolute left-3 bottom-2.5 w-4 h-4 text-gray-400" />
                    <input v-model="filters.q" type="text" placeholder="F.I.O. bo'yicha qidirish..."
                           class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-brand-600 bg-gray-50"
                           @input="debouncedSearch">
                </div>
                <button v-if="filters.q || filters.direction" @click="resetFilters" class="btn-neutral">
                    <Icon icon="mdi:close" class="w-4 h-4" />
                    Tozalash
                </button>
            </div>

            <!-- Jadval -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden" style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div v-if="!events.data?.length" class="p-16 text-center text-gray-400">
                    <Icon icon="mdi:clock-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                    <p class="text-sm">Bu sana/filtr bo'yicha hech qanday voqea topilmadi</p>
                </div>

                <table v-else class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="text-left px-4 py-3">Xodim</th>
                        <th class="text-left px-4 py-3">Sana</th>
                        <th class="text-left px-4 py-3">Vaqt</th>
                        <th class="text-left px-4 py-3">Yo'nalish</th>
                        <th class="text-left px-4 py-3">Terminal</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    <tr v-for="e in events.data" :key="e.id">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ e.staff_name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ e.date }}</td>
                        <td class="px-4 py-3 text-gray-500 font-mono">{{ e.time }}</td>
                        <td class="px-4 py-3">
                                <span class="badge-pill" :class="e.direction === 'chiqish' ? 'badge-warning' : 'badge-success'">
                                    <Icon :icon="e.direction === 'chiqish' ? 'mdi:logout' : 'mdi:login'" class="w-3.5 h-3.5" />
                                    {{ e.direction === 'chiqish' ? 'Chiqish' : (e.direction === 'kirish' ? 'Kirish' : "Noma'lum") }}
                                </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ e.device_name || '—' }}</td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="(events.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-100 flex items-center justify-between flex-wrap gap-3">
                    <p class="text-xs text-gray-500">{{ events.from }}–{{ events.to }} / {{ events.total }}</p>
                    <div class="flex items-center gap-1.5">
                        <template v-for="link in (events.links ?? [])" :key="link.label">
                            <component
                                :is="link.url ? Link : 'span'"
                                :href="link.url ?? undefined"
                                preserve-scroll
                                class="pagination-btn"
                                :class="[link.active ? 'active' : '', !link.url ? 'disabled' : '']"
                            >
                                <Icon v-if="isPrevLabel(link.label)" icon="mdi:chevron-left" class="w-4 h-4" />
                                <Icon v-else-if="isNextLabel(link.label)" icon="mdi:chevron-right" class="w-4 h-4" />
                                <span v-else v-html="link.label" />
                            </component>
                        </template>
                    </div>
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
    events: { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
    stats: { type: Object, default: () => ({ total_events: 0, staff_count: 0 }) },
    filters: { type: Object, default: () => ({}) },
})

const filters = ref({
    date: props.filters.date || new Date().toISOString().slice(0, 10),
    direction: props.filters.direction || '',
    q: props.filters.q || '',
})

const applyFilters = () => {
    router.get(route('admin.attendance.index'), filters.value, {
        preserveState: true, replace: true,
    })
}

const resetFilters = () => {
    filters.value.q = ''
    filters.value.direction = ''
    applyFilters()
}

let searchTimer = null
const debouncedSearch = () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(applyFilters, 400)
}

const isPrevLabel = (label) => /Previous|&laquo;|«/i.test(label)
const isNextLabel = (label) => /Next|&raquo;|»/i.test(label)
</script>
