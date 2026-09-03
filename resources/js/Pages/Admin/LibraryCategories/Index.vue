<template>
    <AppLayout title="Kutubxona kategoriyalari">
        <div class="space-y-5">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Kutubxona kategoriyalari</h1>
                    <p class="text-sm text-gray-500 mt-0.5">Jami: {{ categories.length }} ta kategoriya</p>
                </div>
                <Link :href="route('admin.library-categories.create')" class="btn-primary">
                    <Icon icon="mdi:plus" class="w-4 h-4" />
                    Yangi kategoriya
                </Link>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden"
                 style="box-shadow: 0 2px 8px rgba(0,0,0,0.05)">
                <table class="w-full">
                    <thead>
                    <tr class="border-b border-gray-100">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Kategoriya</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Kitoblar</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                    <tr v-if="!categories.length">
                        <td colspan="4" class="text-center py-16 text-gray-400">
                            <Icon icon="mdi:bookshelf" class="w-12 h-12 mx-auto mb-3 opacity-40" />
                            <p class="text-sm">Kategoriya topilmadi</p>
                        </td>
                    </tr>
                    <tr v-for="c in categories" :key="c.id" class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-white flex-shrink-0"
                                     style="background: linear-gradient(135deg, #0f3460, #533483)">
                                    <Icon icon="mdi:bookshelf" class="w-5 h-5" />
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">{{ c.name_uz }}</p>
                                    <p v-if="c.name_ru || c.name_en" class="text-xs text-gray-400">
                                        {{ [c.name_ru, c.name_en].filter(Boolean).join(' · ') }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl text-sm font-bold"
                                  style="background: linear-gradient(135deg, #eff6ff, #f5f3ff); color: #0f3460">
                                {{ c.books_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold"
                                  :class="c.is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500'">
                                {{ c.is_active ? 'Faol' : 'Nofaol' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <Link :href="route('admin.library-categories.edit', c.id)"
                                      class="text-xs font-medium text-amber-600 hover:text-amber-800 flex items-center gap-1">
                                    <Icon icon="mdi:pencil-outline" class="w-3.5 h-3.5" />
                                    Tahrir
                                </Link>
                                <button @click="confirmDelete(c)"
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
        </div>

        <!-- Delete modal -->
        <div v-if="deleteTarget" class="fixed inset-0 z-50 flex items-center justify-center p-4"
             style="background: rgba(0,0,0,0.5)" @click.self="deleteTarget = null">
            <div class="bg-white rounded-2xl w-full max-w-sm p-6">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mx-auto mb-4">
                    <Icon icon="mdi:delete-outline" class="w-6 h-6 text-red-500" />
                </div>
                <h3 class="text-base font-bold text-gray-900 text-center mb-2">Kategoriyani o'chirish</h3>
                <p class="text-sm text-gray-500 text-center mb-6">
                    <strong>{{ deleteTarget?.name_uz }}</strong> kategoriyasini o'chirasizmi?
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

defineProps({
    categories: { type: Array, default: () => [] },
})

const deleteTarget = ref(null)
const confirmDelete = (c) => { deleteTarget.value = c }
const submitDelete = () => {
    router.delete(route('admin.library-categories.destroy', deleteTarget.value.id), {
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
    justify-content: center;
    padding: 0.625rem 1.25rem;
    border-radius: 0.75rem;
    background: white;
    color: #374151;
    font-size: 0.875rem;
    font-weight: 600;
    border: 1.5px solid #e5e7eb;
    cursor: pointer;
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
