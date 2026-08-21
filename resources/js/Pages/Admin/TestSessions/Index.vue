<template>
    <AppLayout title="Test sessiyalari">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Test sessiyalari</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Jami: {{ sessions.total }} ta sessiya</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl border border-gray-100 p-4 flex flex-wrap gap-3"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <!-- Search -->
                <div class="flex-1 min-w-48 relative">
                    <Icon icon="mdi:magnify" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                    <input
                        v-model="filters.search"
                        type="text"
                        placeholder="Ism yoki pasport seriyasi..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50"
                        @input="debouncedSearch"
                    >
                </div>

                <!-- Status filter -->
                <select
                    v-model="filters.status"
                    class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50"
                    @change="applyFilters"
                >
                    <option value="">Barcha statuslar</option>
                    <option value="pending">Kutilmoqda</option>
                    <option value="active">Faol</option>
                    <option value="completed">Yakunlangan</option>
                    <option value="expired">Muddati o'tgan</option>
                </select>

                <!-- Reset -->
                <button
                    v-if="hasFilters"
                    @click="resetFilters"
                    class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 flex items-center gap-1.5"
                >
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
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Abituriyent</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Login</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Parol</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Yo'nalish</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Til</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Ball</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                        <tr v-if="!sessions.data?.length">
                            <td colspan="8" class="text-center py-16 text-gray-400">
                                <Icon icon="mdi:clipboard-text-off-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                                <p class="text-sm">Test sessiyalari topilmadi</p>
                                <p class="text-xs mt-1">Abituriyent "Test" statusiga o'tganda avtomatik yaratiladi</p>
                            </td>
                        </tr>
                        <tr
                            v-for="session in sessions.data"
                            :key="session.id"
                            class="hover:bg-gray-50 transition-colors"
                        >
                            <!-- Abituriyent -->
                            <td class="px-4 py-3">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ session.applicant?.last_name }} {{ session.applicant?.first_name }}
                                </p>
                                <p class="text-xs text-gray-400 font-mono">{{ session.applicant?.passport_series }}</p>
                            </td>

                            <!-- Login -->
                            <td class="px-4 py-3">
                                    <span class="text-sm font-mono font-semibold text-[#0f3460]">
                                        {{ session.login }}
                                    </span>
                            </td>

                            <!-- Parol -->
                            <td class="px-4 py-3">
                                    <span class="text-sm font-mono text-gray-600">
                                        {{ session.password_plain }}
                                    </span>
                            </td>

                            <!-- Yo'nalish -->
                            <td class="px-4 py-3">
                                <p class="text-xs text-gray-700">{{ session.direction?.name_uz || '—' }}</p>
                                <p class="text-xs text-gray-400">{{ session.direction?.faculty?.short_name || '' }}</p>
                            </td>

                            <!-- Til -->
                            <td class="px-4 py-3">
                                <div class="flex flex-col gap-1">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                              :class="session.language === 'uz' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700'">
                                            {{ session.language === 'uz' ? "O'zbek" : 'Rus' }}
                                        </span>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                            {{ session.foreign_lang === 'en' ? 'Ingliz' : 'Arab' }}
                                        </span>
                                </div>
                            </td>

                            <!-- Ball -->
                            <td class="px-4 py-3">
                                    <span v-if="session.score !== null" class="text-sm font-bold text-green-600">
                                        {{ session.score }}
                                    </span>
                                <span v-else class="text-xs text-gray-400">—</span>
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold"
                                        :class="statusBadge(session.status)"
                                    >
                                        <Icon :icon="statusIcon(session.status)" class="w-3 h-3 mr-1" />
                                        {{ statusLabel(session.status) }}
                                    </span>
                            </td>

                            <!-- Amallar -->
                            <td class="px-4 py-3">
                                <button
                                    @click="confirmDelete(session)"
                                    class="text-xs font-medium text-red-500 hover:text-red-700 flex items-center gap-1"
                                >
                                    <Icon icon="mdi:delete-outline" class="w-3.5 h-3.5" />
                                    O'chirish
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="(sessions.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500">
                        {{ sessions.from }}–{{ sessions.to }} / {{ sessions.total }}
                    </p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in (sessions.links ?? [])" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1.5 text-xs rounded-lg transition"
                                :class="link.active ? 'text-white font-semibold' : 'text-gray-500 hover:bg-gray-100'"
                                :style="link.active ? 'background:linear-gradient(135deg,#0f3460,#533483)' : ''"
                                v-html="link.label"
                            />
                            <span v-else class="px-3 py-1.5 text-xs text-gray-300" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Delete modal -->
        <div
            v-if="deleteTarget"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background: rgba(0,0,0,0.5)"
            @click.self="deleteTarget = null"
        >
            <div class="bg-white rounded-2xl w-full max-w-sm p-6">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <Icon icon="mdi:delete-outline" class="w-6 h-6 text-red-500" />
                </div>
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Sessiyani o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ deleteTarget?.applicant?.last_name }} {{ deleteTarget?.applicant?.first_name }}</strong>
                    ning test sessiyasini o'chirasizmi?
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
    sessions: { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
    filters:  { type: Object, default: () => ({}) },
})

const deleteTarget = ref(null)

const filters = ref({
    search: props.filters.search || '',
    status: props.filters.status || '',
})

const hasFilters = computed(() => filters.value.search || filters.value.status)

const applyFilters = () => {
    router.get(route('admin.test-sessions.index'), filters.value, {
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
    filters.value = { search: '', status: '' }
    applyFilters()
}

const statuses = [
    { value: 'pending',   label: 'Kutilmoqda',       icon: 'mdi:clock-outline',         class: 'bg-yellow-50 text-yellow-700' },
    { value: 'active',    label: 'Faol',              icon: 'mdi:play-circle-outline',   class: 'bg-green-50 text-green-700' },
    { value: 'completed', label: 'Yakunlangan',       icon: 'mdi:check-circle-outline',  class: 'bg-blue-50 text-blue-700' },
    { value: 'expired',   label: "Muddati o'tgan",    icon: 'mdi:alert-circle-outline',  class: 'bg-red-50 text-red-700' },
]

const statusLabel = (s) => statuses.find(x => x.value === s)?.label || s
const statusIcon  = (s) => statuses.find(x => x.value === s)?.icon  || 'mdi:circle'
const statusBadge = (s) => statuses.find(x => x.value === s)?.class || 'bg-gray-50 text-gray-600'

const confirmDelete = (session) => { deleteTarget.value = session }

const submitDelete = () => {
    router.delete(route('admin.test-sessions.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null },
    })
}
</script>

<style scoped>
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
