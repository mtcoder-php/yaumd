<template>
    <AppLayout title="Foydalanuvchilar">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Foydalanuvchilar</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Jami: {{ users.total }} ta foydalanuvchi</p>
                </div>
                <Link :href="route('admin.users.create')" class="btn-primary">
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    Yangi foydalanuvchi
                </Link>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Foydalanuvchi</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Email</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Rol</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Oxirgi kirish</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                    <tr v-if="!users.data?.length">
                        <td colspan="5" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:account-off-outline" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Foydalanuvchi topilmadi</p>
                        </td>
                    </tr>
                    <tr v-for="u in users.data ?? []" :key="u.id"
                        class="hover:bg-gray-50 transition-colors">

                        <!-- Avatar + Ism -->
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center font-bold text-sm text-white flex-shrink-0"
                                     style="background: linear-gradient(135deg, #0f3460, #533483)">
                                    {{ u.full_name?.charAt(0)?.toUpperCase() || 'U' }}
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ u.full_name }}</p>
                                    <p class="text-xs text-gray-400">#{{ u.id }}</p>
                                </div>
                            </div>
                        </td>

                        <!-- Email -->
                        <td class="px-4 py-3 text-sm text-gray-600">{{ u.email }}</td>

                        <!-- Rol -->
                        <td class="px-4 py-3">
                                <span v-for="role in u.roles" :key="role.id"
                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold mr-1"
                                      :class="roleBadge(role.name)">
                                    {{ roleLabel(role.name) }}
                                </span>
                        </td>

                        <!-- Oxirgi kirish -->
                        <td class="px-4 py-3 text-xs text-gray-400">
                            {{ u.last_login_at ? formatDate(u.last_login_at) : 'Hali kirmagan' }}
                        </td>

                        <!-- Amallar -->
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <Link
                                    :href="route('admin.users.edit', u.id)"
                                    class="text-xs font-medium text-amber-600 hover:text-amber-800 flex items-center gap-1">
                                    <Icon icon="mdi:pencil-outline" class="w-3.5 h-3.5" />
                                    Tahrir
                                </Link>
                                <button
                                    v-if="!u.roles?.some(r => r.name === 'super-admin')"
                                    @click="confirmDelete(u)"
                                    class="text-xs font-medium text-red-500 hover:text-red-700 flex items-center gap-1">
                                    <Icon icon="mdi:delete-outline" class="w-3.5 h-3.5" />
                                    O'chirish
                                </button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="(users.last_page ?? 1) > 1"
                     class="px-4 py-3 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-xs text-gray-500">{{ users.from }}–{{ users.to }} / {{ users.total }}</p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in (users.links ?? [])" :key="link.label">
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
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Foydalanuvchini o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ deleteTarget?.full_name }}</strong> ni o'chirasizmi?
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
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { Icon } from '@iconify/vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    users: { type: Object, default: () => ({ data: [], links: [], total: 0 }) },
    roles: { type: Array,  default: () => [] },
})

const deleteTarget = ref(null)

const roles = [
    { value: 'super-admin', label: 'Super Admin', class: 'bg-red-50 text-red-700' },
    { value: 'admin',       label: 'Admin',       class: 'bg-purple-50 text-purple-700' },
    { value: 'admission',   label: 'Qabul',       class: 'bg-blue-50 text-blue-700' },
    { value: 'teacher',     label: "O'qituvchi",  class: 'bg-green-50 text-green-700' },
    { value: 'finance',     label: 'Moliya',      class: 'bg-yellow-50 text-yellow-700' },
    { value: 'librarian',   label: 'Kutubxonachi',class: 'bg-teal-50 text-teal-700' },
    { value: 'student',     label: 'Talaba',      class: 'bg-gray-100 text-gray-600' },
]

const roleLabel = (name) => roles.find(r => r.value === name)?.label || name
const roleBadge = (name) => roles.find(r => r.value === name)?.class || 'bg-gray-100 text-gray-600'

const confirmDelete = (u) => { deleteTarget.value = u }

const submitDelete = () => {
    router.delete(route('admin.users.destroy', deleteTarget.value.id), {
        onSuccess: () => { deleteTarget.value = null },
    })
}

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('uz-UZ', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
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
