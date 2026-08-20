<template>
    <AppLayout title="Audit log">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Audit log</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Tizimda bajariladigan barcha amallar</p>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-2xl border border-gray-100 p-4 flex flex-wrap gap-3"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">

                <!-- Action filter -->
                <select
                    v-model="filters.action"
                    class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50"
                    @change="applyFilters"
                >
                    <option value="">Barcha amallar</option>
                    <option value="login">Login</option>
                    <option value="logout">Logout</option>
                    <option value="created">Yaratildi</option>
                    <option value="updated">Yangilandi</option>
                    <option value="deleted">O'chirildi</option>
                </select>

                <!-- Model filter -->
                <select
                    v-model="filters.model_type"
                    class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50"
                    @change="applyFilters"
                >
                    <option value="">Barcha modellar</option>
                    <option value="Applicant">Abituriyent</option>
                    <option value="User">Foydalanuvchi</option>
                    <option value="Student">Talaba</option>
                </select>

                <!-- Sana filter -->
                <input
                    v-model="filters.date"
                    type="date"
                    class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:outline-none focus:border-[#0f3460] bg-gray-50"
                    @change="applyFilters"
                >

                <!-- Reset -->
                <button
                    v-if="hasFilters"
                    @click="resetFilters"
                    class="px-4 py-2 text-sm text-gray-500 border border-gray-200 rounded-xl hover:bg-gray-50 flex items-center gap-1.5"
                >
                    <Icon icon="mdi:close" class="w-4 h-4" />
                    Tozalash
                </button>

                <div class="ml-auto text-xs text-gray-400 flex items-center">
                    Jami: <span class="font-semibold text-gray-600 ml-1">{{ logs.total }}</span>
                </div>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                        <tr class="border-b border-gray-100">
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Amal</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Foydalanuvchi</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Model</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">IP manzil</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Sana</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                        <tr v-if="!logs.data?.length">
                            <td colspan="6" class="text-center py-16 text-gray-400">
                                <Icon icon="mdi:shield-search" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                                <p class="text-sm">Log yozuvlari topilmadi</p>
                            </td>
                        </tr>
                        <tr
                            v-for="log in logs.data"
                            :key="log.id"
                            class="hover:bg-gray-50 transition-colors"
                        >
                            <!-- Amal -->
                            <td class="px-4 py-3">
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                                        :class="actionBadge(log.action)"
                                    >
                                        <Icon :icon="actionIcon(log.action)" class="w-3 h-3" />
                                        {{ actionLabel(log.action) }}
                                    </span>
                            </td>

                            <!-- Foydalanuvchi -->
                            <td class="px-4 py-3">
                                <p class="text-sm text-gray-800 font-medium">
                                    {{ log.user?.full_name || 'Tizim' }}
                                </p>
                                <p class="text-xs text-gray-400">{{ log.user?.email || '—' }}</p>
                            </td>

                            <!-- Model -->
                            <td class="px-4 py-3">
                                <p class="text-xs text-gray-600">{{ shortModelName(log.model_type) }}</p>
                                <p v-if="log.model_id" class="text-xs text-gray-400">#{{ log.model_id }}</p>
                            </td>

                            <!-- IP -->
                            <td class="px-4 py-3">
                                <span class="text-xs font-mono text-gray-600">{{ log.ip_address || '—' }}</span>
                            </td>

                            <!-- Sana -->
                            <td class="px-4 py-3 text-xs text-gray-400">
                                {{ formatDate(log.created_at) }}
                            </td>

                            <!-- Detail -->
                            <td class="px-4 py-3">
                                <button
                                    v-if="log.old_values || log.new_values"
                                    @click="openDetail(log)"
                                    class="text-xs font-medium flex items-center gap-1 transition"
                                    style="color: #0f3460"
                                >
                                    <Icon icon="mdi:eye-outline" class="w-3.5 h-3.5" />
                                    Detail
                                </button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="(logs.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500">
                        {{ logs.from }}–{{ logs.to }} / {{ logs.total }}
                    </p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in (logs.links ?? [])" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1.5 text-xs rounded-lg transition"
                                :class="link.active ? 'text-white font-semibold' : 'text-gray-500 hover:bg-gray-100'"
                                :style="link.active ? 'background:linear-gradient(135deg,#0f3460,#533483)' : ''"
                                v-html="link.label"
                            />
                            <span v-else class="px-3 py-1.5 text-xs rounded-lg text-gray-300" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>

        </div>

        <!-- Detail modal -->
        <div
            v-if="selectedLog"
            class="fixed inset-0 z-50 flex items-center justify-center p-4"
            style="background: rgba(0,0,0,0.5)"
            @click.self="selectedLog = null"
        >
            <div class="bg-white rounded-2xl w-full max-w-2xl max-h-[80vh] overflow-hidden flex flex-col">
                <!-- Modal header -->
                <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                              :class="actionBadge(selectedLog.action)">
                            <Icon :icon="actionIcon(selectedLog.action)" class="w-3 h-3" />
                            {{ actionLabel(selectedLog.action) }}
                        </span>
                        <span class="text-sm text-gray-500">{{ shortModelName(selectedLog.model_type) }} #{{ selectedLog.model_id }}</span>
                    </div>
                    <button @click="selectedLog = null" class="text-gray-400 hover:text-gray-600">
                        <Icon icon="mdi:close" class="w-5 h-5" />
                    </button>
                </div>

                <!-- Modal body -->
                <div class="overflow-y-auto p-5 space-y-4">
                    <!-- Old values -->
                    <div v-if="selectedLog.old_values">
                        <p class="text-xs font-semibold text-red-500 uppercase tracking-wider mb-2">Eski qiymatlar</p>
                        <div class="bg-red-50 rounded-xl p-3 space-y-1.5">
                            <div v-for="(val, key) in selectedLog.old_values" :key="key"
                                 class="flex items-start gap-2 text-xs">
                                <span class="font-mono text-gray-500 min-w-32">{{ key }}</span>
                                <span class="text-red-700 break-all">{{ val }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- New values -->
                    <div v-if="selectedLog.new_values">
                        <p class="text-xs font-semibold text-green-600 uppercase tracking-wider mb-2">Yangi qiymatlar</p>
                        <div class="bg-green-50 rounded-xl p-3 space-y-1.5">
                            <div v-for="(val, key) in selectedLog.new_values" :key="key"
                                 class="flex items-start gap-2 text-xs">
                                <span class="font-mono text-gray-500 min-w-32">{{ key }}</span>
                                <span class="text-green-700 break-all">{{ val }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Meta -->
                    <div class="border-t border-gray-100 pt-3 grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-gray-400 mb-1">Foydalanuvchi</p>
                            <p class="text-sm font-medium text-gray-800">{{ selectedLog.user?.full_name || 'Tizim' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 mb-1">IP manzil</p>
                            <p class="text-sm font-mono text-gray-800">{{ selectedLog.ip_address }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs text-gray-400 mb-1">User Agent</p>
                            <p class="text-xs text-gray-600 break-all">{{ selectedLog.user_agent }}</p>
                        </div>
                    </div>
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
    logs:    { type: Object, default: () => ({ data: [], links: [], total: 0, last_page: 1 }) },
    filters: { type: Object, default: () => ({}) },
})

const selectedLog = ref(null)

const filters = ref({
    action:     props.filters.action     || '',
    model_type: props.filters.model_type || '',
    date:       props.filters.date       || '',
})

const hasFilters = computed(() =>
    filters.value.action || filters.value.model_type || filters.value.date
)

const applyFilters = () => {
    router.get(route('admin.audit-logs.index'), filters.value, {
        preserveState: true,
        replace: true,
    })
}

const resetFilters = () => {
    filters.value = { action: '', model_type: '', date: '' }
    applyFilters()
}

const openDetail = (log) => {
    selectedLog.value = log
}

// Action
const actionLabel = (action) => {
    const labels = {
        login:   'Login',
        logout:  'Logout',
        created: 'Yaratildi',
        updated: 'Yangilandi',
        deleted: "O'chirildi",
    }
    return labels[action] || action
}

const actionIcon = (action) => {
    const icons = {
        login:   'mdi:login',
        logout:  'mdi:logout',
        created: 'mdi:plus-circle-outline',
        updated: 'mdi:pencil-outline',
        deleted: 'mdi:delete-outline',
    }
    return icons[action] || 'mdi:circle-outline'
}

const actionBadge = (action) => {
    const badges = {
        login:   'bg-blue-50 text-blue-700',
        logout:  'bg-gray-100 text-gray-600',
        created: 'bg-green-50 text-green-700',
        updated: 'bg-yellow-50 text-yellow-700',
        deleted: 'bg-red-50 text-red-700',
    }
    return badges[action] || 'bg-gray-50 text-gray-600'
}

const shortModelName = (modelType) => {
    if (!modelType) return '—'
    return modelType.split('\\').pop()
}

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleString('uz-UZ', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit', second: '2-digit'
    })
}
</script>
