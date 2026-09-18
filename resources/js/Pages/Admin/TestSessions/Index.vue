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
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-brand-600 bg-gray-50"
                        @input="debouncedSearch"
                    >
                </div>

                <!-- Status filter -->
                <select v-model="filters.status" class="select-filter" @change="applyFilters">
                    <option value="">Barcha statuslar</option>
                    <option value="pending">Kutilmoqda</option>
                    <option value="active">Faol</option>
                    <option value="completed">Yakunlangan</option>
                    <option value="expired">Muddati o'tgan</option>
                </select>

                <!-- Reset filters -->
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
                        <th>Abituriyent</th>
                        <th>Login</th>
                        <th>Parol</th>
                        <th>Yo'nalish</th>
                        <th>Til</th>
                        <th class="text-center">Ball</th>
                        <th>Status</th>
                        <th class="text-right">Amallar</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-if="!sessions.data?.length">
                        <td colspan="8" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:clipboard-text-off-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Test sessiyalari topilmadi</p>
                            <p class="text-xs mt-1">Abituriyent "Test" statusiga o'tganda avtomatik yaratiladi</p>
                        </td>
                    </tr>
                    <tr v-for="session in sessions.data" :key="session.id">
                        <!-- Abituriyent -->
                        <td>
                            <p class="text-sm font-medium text-gray-900">
                                {{ session.applicant?.last_name }} {{ session.applicant?.first_name }}
                            </p>
                            <p class="text-xs text-gray-400 font-mono">{{ session.applicant?.passport_series }}</p>
                        </td>

                        <!-- Login -->
                        <td>
                            <span class="text-sm font-mono font-semibold text-brand-600">
                                {{ session.login }}
                            </span>
                        </td>

                        <!-- Parol -->
                        <td>
                            <span class="text-sm font-mono text-gray-600">
                                {{ session.password_plain }}
                            </span>
                        </td>

                        <!-- Yo'nalish -->
                        <td>
                            <p class="text-xs text-gray-700">{{ session.direction?.name_uz || '—' }}</p>
                            <p class="text-xs text-gray-400">{{ session.direction?.faculty?.short_name || '' }}</p>
                        </td>

                        <!-- Til -->
                        <td>
                            <div class="flex flex-col gap-1">
                                <span class="badge-pill" :class="session.language === 'uz' ? 'badge-brand' : 'badge-neutral'">
                                    {{ session.language === 'uz' ? "O'zbek" : 'Rus' }}
                                </span>
                                <span class="badge-pill badge-neutral">
                                    {{ session.foreign_lang === 'en' ? 'Ingliz' : 'Arab' }}
                                </span>
                            </div>
                        </td>

                        <!-- Ball -->
                        <td class="text-center">
                            <span v-if="session.score !== null" class="text-sm font-bold text-green-600">
                                {{ session.score }}
                            </span>
                            <span v-else class="text-xs text-gray-400">—</span>
                        </td>

                        <!-- Status -->
                        <td>
                            <span class="badge-pill" :class="statusBadge(session.status)">
                                <Icon :icon="statusIcon(session.status)" class="w-3 h-3" />
                                {{ statusLabel(session.status) }}
                            </span>
                        </td>

                        <!-- Amallar -->
                        <td>
                            <div class="flex items-center justify-end gap-1">
                                <button @click="confirmReset(session)" title="Qayta berish" class="btn-ghost-icon">
                                    <Icon icon="mdi:refresh" class="w-4 h-4" />
                                </button>
                                <button @click="confirmDelete(session)" title="O'chirish" class="btn-ghost-icon danger">
                                    <Icon icon="mdi:delete-outline" class="w-4 h-4" />
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="(sessions.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-200 flex items-center justify-between flex-wrap gap-3">
                    <p class="text-xs text-gray-500">{{ sessions.from }}–{{ sessions.to }} / {{ sessions.total }}</p>
                    <div class="flex items-center gap-1.5">
                        <template v-for="link in (sessions.links ?? [])" :key="link.label">
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

        <!-- Reset modal -->
        <div v-if="resetTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="resetTarget = null">
            <div class="bg-white rounded-2xl w-full max-w-sm p-6">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mx-auto mb-4">
                    <Icon icon="mdi:refresh" class="w-6 h-6 text-blue-500" />
                </div>
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Qayta test berish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ resetTarget?.applicant?.last_name }} {{ resetTarget?.applicant?.first_name }}</strong>
                    ga yangi test sessiyasi yaratilib, eski natija o'chiriladi. Davom etasizmi?
                </p>
                <div class="flex gap-3">
                    <button @click="resetTarget = null" class="btn-neutral flex-1 justify-center">Bekor qilish</button>
                    <button @click="submitReset" class="btn-brand flex-1 justify-center">Qayta berish</button>
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
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Sessiyani o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ deleteTarget?.applicant?.last_name }} {{ deleteTarget?.applicant?.first_name }}</strong>
                    ning test sessiyasini o'chirasizmi?
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
    sessions: { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
    filters:  { type: Object, default: () => ({}) },
})

const deleteTarget = ref(null)
const resetTarget  = ref(null)

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
    { value: 'pending',   label: 'Kutilmoqda',    icon: 'mdi:clock-outline',        class: 'badge-warning' },
    { value: 'active',    label: 'Faol',           icon: 'mdi:play-circle-outline',  class: 'badge-success' },
    { value: 'completed', label: 'Yakunlangan',    icon: 'mdi:check-circle-outline', class: 'badge-brand' },
    { value: 'expired',   label: "Muddati o'tgan", icon: 'mdi:alert-circle-outline', class: 'badge-danger' },
]

const statusLabel = (s) => statuses.find(x => x.value === s)?.label || s
const statusIcon  = (s) => statuses.find(x => x.value === s)?.icon  || 'mdi:circle'
const statusBadge = (s) => statuses.find(x => x.value === s)?.class || 'badge-neutral'

const confirmDelete = (session) => { deleteTarget.value = session }
const confirmReset  = (session) => { resetTarget.value  = session }

const submitDelete = () => {
    router.delete(route('admin.test-sessions.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null },
    })
}

const submitReset = () => {
    router.post(route('admin.test-sessions.reset', resetTarget.value.id), {}, {
        onSuccess: () => { resetTarget.value = null },
    })
}

// Laravel'ning standart pagination yorliqlari o'rniga sof strelka
// ikonkalarini ko'rsatish uchun (Foydalanuvchilar sahifasidagi bilan
// bir xil naqsh).
const isPrevLabel = (label) => /Previous|&laquo;|«/i.test(label)
const isNextLabel = (label) => /Next|&raquo;|»/i.test(label)
</script>
